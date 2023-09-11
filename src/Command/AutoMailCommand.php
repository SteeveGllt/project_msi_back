<?php

namespace App\Command;

use App\Entity\Etat;
use App\Entity\PieceJointe;
use App\Entity\Salle;
use App\Entity\Ticket;
use App\Entity\TicketLogs;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use \GuzzleHttp;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;


#[AsCommand(
    name: 'app:auto-mail',
    description: 'Créer automatiquement un ticket pour un mail',
)]
class AutoMailCommand extends Command
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $entityManager,LoggerInterface $logger) {
        parent::__construct();
        $this->em = $entityManager;
        $this->logger = $logger;
    }
    protected function configure(): void
    {
        $this->setName('app:auto-mail')
            ->setDescription('Créer automatiquement un ticket pour un mail');
    }

    public function getToken()
    {
        $guzzle = new GuzzleHttp\Client();

        $clientId = "55fe901e-3e2a-4421-8cf4-dc1899afb9c4";
        $clientSecret ="6QR8Q~BwTzTmyMFlf3QfpeUU.nLMg4yItVrgLa.r";//VALABLE 2 ANS !!! A CHANGER COURANT NOVEMBRE 2024
        $tenantId = "8f8c2d87-4ccb-4c43-b549-9a26925d1ee3";
        $url = "https://login.microsoftonline.com/". $tenantId ."/oauth2/v2.0/token";

        $token = $guzzle->request('POST' , $url , [
            'form_params' => [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ]
        ]);

        $accessToken = json_decode($token->getBody());

        return $accessToken;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $accessToken = $this->getToken()->access_token;

        $client = new GuzzleHttp\Client();
        $res = $client->get("https://graph.microsoft.com/v1.0/users/glpi@groupemontroland.fr/mailFolders/AAMkADk4ZGFhMTM4LTU3NTItNDg1Mi05ZDBlLTgzY2ZlZjkwODEyMgAuAAAAAADMn5JomEupQ75VkWTIzSZPAQCc2agFhYKkRZxjQV7hJpY8AAAAAAEMAAA=/messages",[
            'headers' => ['Authorization' => 'Bearer '.$accessToken, 'Prefer' => "outlook.body-content-type='text'"]
        ]);//L'ID du dossier dans lequel on veux récupéré les mails pour ne pas avoir les mails du dossier archive

        //$response = tous les mails
        $response = json_decode($res->getBody()->getContents());

        //L'heure des mails que l'on reçoit sont en UTC hors la France est en UTC + 1 ou UTC + 2
        //Pour savoir dans quel UTC somme nous (car fuck le changement d'heure), on compare l'heure de Paris avec l'heure de Reykjavik en Islande
        //Car l'Islande ne change jamais d'heure et est donc toujours en UTC
        $heureAjouter = 0;
        $paris = strtotime("today 9:00 am Europe/Paris");
        $reykjavik = strtotime("today 9:00 am Atlantic/Reykjavik");
        $heureAjouter = 0;
        if($reykjavik - $paris === 7200){
            $heureAjouter = 2;//Si l'écrat est de 2 heures (7200 s) alors on ajoutera 2h à la date de création du ticket
        }else{
            $heureAjouter = 1;//Sinon on est en UTC + 1 donc on ajoute une heure
        }
        //On récupère le dernier ticket qui a été inséré dans la base
        $lastTicket = $this->em->getRepository(Ticket::class)->getLastInsert();
        //On récupère l'état vu pour l'assigner aux nouveaux etats
        $etatBase = $this->em->getRepository(Etat::class)->getEtatVu();
        $salle = $this->em->getRepository(Salle::class)->getSalleBase();
        /*
        Pour chaque mail, on va donc vérifier si il est plus récent que le dernier ticket.
        Si c'est le cas, il n'a pas été transformé en ticket donc on s'en charge
        */
        $count = 0;
        $attachements = 0;
        foreach ($response->value as $mail)
        {
            $dateMail = new \DateTime($mail->receivedDateTime);
            $dateMail->add(new \DateInterval("PT".$heureAjouter."H"));
            if($dateMail->format('Y-m-d H:i:s') > $lastTicket[0]->getDateCreation()->format('Y-m-d H:i:s')) //Pour comparé des dates il faut les mettres au format Y-m-d H:i:s
            {
                //Création du ticket
                $newTicket = new Ticket();
                $newTicket->setMailExpediteur($mail->from->emailAddress->address);
                $mailsDestinaires = array();
                foreach ($mail->toRecipients as $recipient){
                    array_push($mailsDestinaires,$recipient->emailAddress->address);
                }
                if($mail->ccRecipients != null){
                    foreach ($mail->ccRecipients as $ccRecipient){
                        array_push($mailsDestinaires,$ccRecipient->emailAddress->address); //Les adresses mails en copie
                    }
                }
                $newTicket->setMailDestinataire($mailsDestinaires);

                //Si pas d'objet on met "Pas d'objet"
                if($mail->subject != null){
                    $newTicket->setObjet($mail->subject);
                }
                else{
                    $newTicket->setObjet("Pas d'objet");
                }

                $newTicket->setDescription($mail->body->content);
                $newTicket->setDateCreation($dateMail);
                $dateLimite = new \DateTime($mail->receivedDateTime);
                $dateLimite->add(new \DateInterval('P1W'));
                $newTicket->setDateLimite($dateLimite);//Une semaine de date limite de base
                $newTicket->setEtat($etatBase);
                $newTicket->setRepondu(false);
                $newTicket->setSalle($salle);

                //Récupération des pièces jointes d'un mail
                if($mail->hasAttachments === true){
                    $res = $client->get("https://graph.microsoft.com/v1.0/users/glpi@groupemontroland.fr/messages/".$mail->id."/attachments",[
                        'headers' => ['Authorization' => 'Bearer '.$accessToken]]);
                    $resJointes = json_decode($res->getBody()->getContents());
                    //On boucle sur chaque pièce jointe
                    foreach ($resJointes->value as $pieceJointe){
                        try{
                            $url = "https://graph.microsoft.com/v1.0/users/glpi@groupemontroland.fr/messages/".$mail->id."/attachments/".$pieceJointe->id.'/$value';
                            $originalName = substr($pieceJointe->name, 0, strpos($pieceJointe->name, ".")); //On récupère le nom du fichier sans l'extension
                            $originalExt = substr($pieceJointe->name, strpos($pieceJointe->name, "."));
                            $importName = $originalName .uniqid() . $originalExt;
                            try {
                                $ressource = fopen("public/PiecesJointes/".$importName,'w');
                                $client->request('GET',$url,[
                                    'headers' => [
                                        'Authorization' => 'Bearer ' . $accessToken,
                                    ],
                                    'sink'=> $ressource
                                ]);
                                $this->logger->error("Image ".$importName." téléchargée avec succès");
                            }catch (\Exception $e){
                                $this->logger->error("Erreur lors du téléchargement de l'image.$importName");
                                $this->logger->error($e->getMessage());
                            }

                            $newPieceJointe = new PieceJointe();
                            $newPieceJointe->setPath("./PiecesJointes/".$importName);
                            $newPieceJointe->addTicket($newTicket);
                            $this->em->persist($newPieceJointe);
                        }catch (\Exception $e){
                            $output->writeln($e->getMessage());
                        }
                    }
                }
                $this->em->persist($newTicket);
                $this->em->flush();
                $count++;
            }
        }
        $logs = $this->em->getRepository(TicketLogs::class)->findAll();
        if($count > 0){
            $logs[0]->setIsLastNew(true);
        }else{
            $logs[0]->setIsLastNew(false);
        }
        $this->em->persist($logs[0]);
        $this->em->flush();
        $output->writeln($count.' nouveaux tickets ont été créés');
        return Command::SUCCESS;
    }
}
