<?php

namespace App\Controller;

use App\Command\AutoMailCommand;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use \GuzzleHttp;
use Microsoft\Graph\Model;
use Microsoft\Graph\Graph;

class MailController extends AbstractController
{

    private EntityManagerInterface $em;
    private MailerInterface $mailer;

    public function __construct(EntityManagerInterface $entityManager,MailerInterface $mailerNew) {
        $this->em = $entityManager;
        $this->mailer = $mailerNew;
    }

    #[Route('/api/transfer-travaux/{idTicket}', name: 'app_mail',methods: "post")]
    public function index($idTicket,Request $request): JsonResponse
    {
        $data = $request->toArray();
        $ticket = $this->em->getRepository(Ticket::class)->find($idTicket);
        if($ticket->getPieceJointes() != null){
            $piecesJointes = $ticket->getPieceJointes();
        }
        $note = '';
        try{
            if($data['note'] != ""){
                $note = '<div class="note"><p> Note de : '.$data['user']['prenom'].' '.$data['user']['nom'].'</p>
                            <p>'.$data['note'].'</p></div>';
            }else{
                $note = '<div></div>';
            }
            $mailDestinataire = "epaul@groupemontroland.fr";
            $mail = (new Email())
                    ->from('serviceinformatique@groupemontroland.fr')
                    ->to($mailDestinataire)
                    ->subject($ticket->getObjet())
                    ->html('
                        <!DOCTYPE html>
                        <html lang="fr"> 
                        <head> 
                        </head>
                        <body> 
                        <div> <p>Bonjour, on aurait besoin de vous</p>
                        '.$note.'
                        <div class="message-container">
                            <p><b>Mail de : </b>' . $ticket->getMailExpediteur() . '</p><div>' . $data["description"] . '</div>
                        </div>
                        <div> <p>Merci beaucoup</p><p>Le service info</p></div>
                        </div>
                        </body> 
                        <style>.message-container{margin-top: 1em; display: flex; flex-direction: column; text-align: center; background-color: #e8e8e8; border-radius: 1%; width: auto; height: auto; word-wrap: break-word;}.message-container div{margin: 2em;}
                        .note{
                            display: flex; flex-direction: column; text-align: center;background-color: #1185f4;color:white; border-radius: 1%; width: auto; height: auto; word-wrap: break-word;
                        }
                        </style> 
                        </html>
                    ');

            foreach ($piecesJointes as $jointe){
                $mail->attachFromPath($jointe->getPath());
            }
            $this->mailer->send($mail);
            return new JsonResponse([
                'status' => 'success',
                'code' => 200
            ]);
        }catch (\Exception $e){
            return new JsonResponse($e->getMessage());
        }
    }
}
