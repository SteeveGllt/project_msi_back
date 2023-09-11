<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Salle;
use App\Entity\User;
use OpenApi\Attributes as OA;
use App\Entity\Etat;
use App\Entity\Ticket;
use Container3NgyirF\getSecurity_Firewall_Map_Context_LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class TicketController extends AbstractController
{
    private EntityManagerInterface $em;


    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    #[OA\Get(path: '/api/tickets',
        description: "Cette route permet de retourner tous les tickets présentes en base de données au format Json.",
        tags: ["Ticket"],
        responses: [
            new OA\Response(response: "200", description: "Retourne tous les tickets au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/tickets', name: 'app_ticket')]
    public function index(): JsonResponse
    {
        $tickets = $this->em->getRepository(Ticket::class)->findAll();

        foreach ($tickets as $ticket){
            $userArray = [];
            $arrayComments = [];
            $commentUser = [];
            $etat = $ticket->getEtat();
            $salle = $ticket->getSalle();

            $color = $etat->getColor();
            list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
            if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                $fontColor = "2F323A";
            }else{
                $fontColor = "ffffff";
            }
            $etatArray = array(
                'id' => $etat->getId(),
                'libelle' => $etat->getLibelle(),
                'ordre' =>$etat->getOrdre(),
                'color' => $etat->getColor(),
                'fontColor' => $fontColor,
            );
            $salleArray = array(
                'id' => $salle->getId(),
                'libelle' => $salle->getLibelle(),
            );
            //On récup les users taggué sur le ticket si il y en a
            if(count($ticket->getUtilisateur()) != 0){
                $users = $ticket->getUtilisateur();
                foreach ($users as $user){
                    $color = $user->getColor();
                    list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                    if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                        $fontColor = "2F323A";
                    }else{
                        $fontColor = "ffffff";
                    }
                    $userArray[] = array(
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'nom' =>$user->getNom(),
                        'prenom' =>$user->getPrenom(),
                        'color' => $color,
                        'fontColor' => $fontColor
                    );
                }
            }
            //Pareil avec les commentaires
            if(count($ticket->getCommentaire()) != 0){
                $comments = $ticket->getCommentaire();
                foreach ($comments as $comment){
                    //On charge l'utilisateur dont provient le commentaire
                    $commentUser = array(
                        'id' => $comment->getUtilisateur()->getId(),
                        'email' => $comment->getUtilisateur()->getEmail(),
                        'nom' =>$comment->getUtilisateur()->getNom(),
                        'prenom' =>$comment->getUtilisateur()->getPrenom(),
                    );


                    $arrayComments[] = array(
                        'id' => $comment->getId(),
                        'contenu' =>$comment->getContenu(),
                        'created' =>$comment->getCreated()->format('d-m-Y H:i'),
                        'user' => $commentUser
                    );
                }
            }
            $arrayTickets[] = array(
                'id' => $ticket->getId(),
                'mail_expediteur' => $ticket->getMailExpediteur(),
                'mail_destinataire' => $ticket->getMailDestinataire(),
                'objet' => $ticket->getObjet(),
                'description' => $ticket->getDescription(),
                'date_creation' => $ticket->getDateCreation()->format('d-m-Y H:i'),
                'date_limite' => $ticket->getDateLimite()->format('d-m-Y'),
                'is_repondu' => $ticket->isRepondu(),
                'etat' => $etatArray,
                'salle' => $salleArray,
                'users' => $userArray,
                'commentaires' =>$arrayComments
            );
        }
        return new JsonResponse($arrayTickets);
    }

    #[OA\Get(path: '/api/ticket/{id}',
        description: "Cette route permet de retourner le ticket dont l'id est passé en paramètre au format Json.",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id du ticket que nous voulons récupérer !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne tous le tickets au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/ticket/{id}', name: 'app_one_ticket')]
    public function indexOne(int $id):JsonResponse
    {
        $ticket = $this->em->getRepository(Ticket::class)->find($id);
        $etat = $ticket->getEtat();
        $salle = $ticket->getSalle();
        $userArray = [];
        $arrayComments = [];
        $commentUser = [];

        $color = $etat->getColor();
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
            $fontColor = "2F323A";
        }else{
            $fontColor = "ffffff";
        }
        $etatArray = array(
            'id' => $etat->getId(),
            'libelle' => $etat->getLibelle(),
            'ordre' =>$etat->getOrdre(),
            'color' => $etat->getColor(),
            'fontColor' => $fontColor,
        );
        $salleArray = array(
            'id' => $salle->getId(),
            'libelle' => $salle->getLibelle(),
        );
        if($ticket->getUtilisateur()->count() != 0 ){
            $users = $ticket->getUtilisateur();
            foreach ($users as $user){
                $color = $user->getColor();
                list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                    $fontColor = "2F323A";
                }else{
                    $fontColor = "ffffff";
                }
                $userArray[] = array(
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nom' =>$user->getNom(),
                    'prenom' =>$user->getPrenom(),
                    'color' => $color,
                    'fontColor' => $fontColor
                );
            }
        }
        if($ticket->getCommentaire()->count() != 0){
            $comments = $ticket->getCommentaire();
            foreach ($comments as $comment){
                $commentUser = array(
                    'id' => $comment->getUtilisateur()->getId(),
                    'email' => $comment->getUtilisateur()->getEmail(),
                    'nom' =>$comment->getUtilisateur()->getNom(),
                    'prenom' =>$comment->getUtilisateur()->getPrenom(),
                );

                $arrayComments[] = array(
                    'id' => $comment->getId(),
                    'contenu' =>$comment->getContenu(),
                    'created' =>$comment->getCreated()->format('d-m-Y H:i'),
                    'user' =>$commentUser
                );
            }
        }
        $arrayTickets = array(
            'id' => $ticket->getId(),
            'mail_expediteur' => $ticket->getMailExpediteur(),
            'mail_destinataire' => $ticket->getMailDestinataire(),
            'objet' => $ticket->getObjet(),
            'description' => $ticket->getDescription(),
            'date_creation' => $ticket->getDateCreation()->format('d-m-Y H:i'),
            'date_limite' => $ticket->getDateLimite()->format('d-m-Y'),
            'is_repondu' => $ticket->isRepondu(),
            'etat' => $etatArray,
            'salle' => $salleArray,
            'users' =>$userArray,
            'commentaires' => $arrayComments
        );
        return new JsonResponse($arrayTickets);
    }

    #[OA\Post(path: '/api/create-ticket',
        description: "Cette route permet de modifier un ticket déjà existant en base de donnée.",
        tags: ["Ticket"],
        responses: [
            new OA\Response(response: "200", description: "Retourne au format Json le ticket qui vient d'être modifié et enregistrer en base de données.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/create-ticket', name: 'app_create_ticket',methods: "post")]
    public function create(Request $request):JsonResponse
    {
        $data = $request->toArray();
        $datecreation = new \DateTime('now');
        if($data["date_limite"] !== null){
            try {
                $datelimite = new \DateTime($data["date_limite"]);
            }catch (Exception $e){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => 'format de date invalide'
                ]);
            }

        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'date limite invalide'
            ]);
        }

        try {
            $etat = $this->em->getRepository(Etat::class)->findOneBy(["id" => $data["etat"]["id"]]);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'état inexistant'
            ]);
        }
        try {
            $salle = $this->em->getRepository(Salle::class)->findOneBy(["id" => $data["salle"]["id"]]);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'Pas de salle'
            ]);
        }

        $ticket = new Ticket();

        if($data["mail_expediteur"] !== null && $data["mail_expediteur"] !== ""){
            $ticket->setMailExpediteur($data["mail_expediteur"]);
        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'mail expéditeur inexistant'
            ]);
        }
        if($data["mail_destinataire"] !== [] && $data["mail_destinataire"] !== null){
            $ticket->setMailDestinataire($data["mail_destinataire"]);
        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'mail destinataire inexistant'
            ]);
        }

        try {
            $data['id'];
        }catch (Exception $e) {
            $data['id'] = -1;
        }

        if($data['id'] !== -1){
            try {
                $ticket->setObjet("Copie ".$data["objet"]);
                $ticketOriginel = $this->em->getRepository(Ticket::class)->find($data['id']);
                $piecesJointes = $ticketOriginel->getPieceJointes();
                foreach ($piecesJointes as $jointe) {
                    $ticket->addPieceJointe($jointe);
                }
            }catch (Exception $e){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => "Pas d'objet"
                ]);
            }
        }else{
            try {
                $ticket->setObjet($data["objet"]);
            }catch (Exception $e){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => "Pas d'objet"
                ]);
            }
        }

        if($data["description"] !== null && $data["description"] !== ""){
            $ticket->setDescription($data["description"]);
        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'pas de description'
            ]);
        }

        if($data["is_repondu"] !== null && $data["is_repondu"] !== ""){
            $ticket->setRepondu($data["is_repondu"]);
        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'isRepondu null'
            ]);
        }

        if($data["users"] != null){
            foreach ($data["users"] as $user){
                $tempUser = $this->em->getRepository(User::class)->find($user["id"]);
                $ticket->addUtilisateur($tempUser);
            }
        }

        $ticket->setDateCreation($datecreation);
        $ticket->setDateLimite($datelimite);
        $ticket->setEtat($etat);
        $ticket->setSalle($salle);
        $this->em->persist($ticket);
        $this->em->flush();

        $arrayComments = [];
        if($ticket->getCommentaire()->count() != 0){
            $comments = $ticket->getCommentaire();
            foreach ($comments as $comment){
                $commentUser = array(
                    'id' => $comment->getUtilisateur()->getId(),
                    'email' => $comment->getUtilisateur()->getEmail(),
                    'nom' =>$comment->getUtilisateur()->getNom(),
                    'prenom' =>$comment->getUtilisateur()->getPrenom(),
                );

                $arrayComments[] = array(
                    'id' => $comment->getId(),
                    'contenu' =>$comment->getContenu(),
                    'created' =>$comment->getCreated()->format('d-m-Y H:i'),
                    'user' =>$commentUser
                );
            }
        }
        $salleArray = array(
            'id' => $salle->getId(),
            'libelle' => $salle->getLibelle(),
        );

        $color = $etat->getColor();
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
            $fontColor = "2F323A";
        }else{
            $fontColor = "ffffff";
        }
        $arrayEtat = array(
            'id' => $etat->getId(),
            'libelle' => $etat->getLibelle(),
            'ordre' =>$etat->getOrdre(),
            'color' => $etat->getColor(),
            'fontColor' => $fontColor
        );
        $userArray = [];
        if($ticket->getUtilisateur() != null) {
            foreach ($ticket->getUtilisateur() as $user){
                $color = $user->getColor();
                list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                    $fontColor = "2F323A";
                }else{
                    $fontColor = "ffffff";
                }
                $userArray[] = array(
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nom' =>$user->getNom(),
                    'prenom' =>$user->getPrenom(),
                    'color' => $color,
                    'fontColor' => $fontColor
                );
            }
        }
        $arrayTickets = array(
            'id' => $ticket->getId(),
            'mail_expediteur' => $ticket->getMailExpediteur(),
            'mail_destinataire' => $ticket->getMailDestinataire(),
            'objet' => $ticket->getObjet(),
            'description' => $ticket->getDescription(),
            'date_creation' => $ticket->getDateCreation()->format('Y-m-d H:i'),
            'date_limite' => $ticket->getDateLimite()->format('Y-m-d'),
            'is_repondu' => $ticket->isRepondu(),
            'etat' => $arrayEtat,
            'salle' => $salleArray,
            'commentaires' => $arrayComments,
            'users' =>$userArray
        );
        return new JsonResponse($arrayTickets);
    }
    #[OA\Put(path: '/api/edit-ticket/{id}',
        description: "Cette route permet de modifier un ticket déjà existant en base de donnée.",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id du ticket que nous voulons modifier !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne au format Json l'état qui vient d'être modifié et enregistrer en base de données.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
            new OA\Response(response: "500", description: "Retourne au format Json une erreur ainsi que son message"),
        ]
    )]
    #[Route('/api/edit-ticket/{id}', name: 'app_edit_ticket',methods: "put|patch")]
    public function edit(int $id,Request $request):JsonResponse
    {
        $data = $request->toArray();
        try {
            $ticket = $this->em->getRepository(Ticket::class)->find($id);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'ticket inexistant'
            ]);
        }

        if($data["date_limite"] !== null){
            try {
                $datelimite = new \DateTime($data["date_limite"]);
            }catch (Exception $e){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => 'format de date invalide'
                ]);
            }

        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'date limite invalide'
            ]);
        }

        try {
            $ticket->setObjet($data["objet"]);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "Pas d'objet"
            ]);
        }

        try {
            $ticket->setMailExpediteur($data["mail_expediteur"]);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "Pas de mail expediteur"
            ]);
        }

        try {
            $ticket->setDescription($data["description"]);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "Pas de mail expediteur"
            ]);
        }
        $ticket->setDateLimite($datelimite);

        $this->em->persist($ticket);
        $this->em->flush();

        $arrayComments = array();
        if($ticket->getCommentaire()->count() != 0){
            $comments = $ticket->getCommentaire();
            foreach ($comments as $comment){
                $commentUser = array(
                    'id' => $comment->getUtilisateur()->getId(),
                    'email' => $comment->getUtilisateur()->getEmail(),
                    'nom' =>$comment->getUtilisateur()->getNom(),
                    'prenom' =>$comment->getUtilisateur()->getPrenom(),
                );

                $arrayComments[] = array(
                    'id' => $comment->getId(),
                    'contenu' =>$comment->getContenu(),
                    'created' =>$comment->getCreated()->format('d-m-Y H:i'),
                    'user' =>$commentUser
                );
            }
        }
        $userArray = array();
        if($ticket->getUtilisateur()->count() != 0 ){
            $users = $ticket->getUtilisateur();

            foreach ($users as $user){
                $color = $user->getColor();
                list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                    $fontColor = "2F323A";
                }else{
                    $fontColor = "ffffff";
                }
                $userArray[] = array(
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'nom' =>$user->getNom(),
                    'prenom' =>$user->getPrenom(),
                    'color' => $color,
                    'fontColor' => $fontColor
                );
            }
        }
        $salleTicket = array(
            'id' => $ticket->getSalle()->getId(),
            'libelle' => $ticket->getSalle()->getLibelle(),
        );
        $arrayTickets = array(
            'id' => $id,
            'mail_expediteur' => $ticket->getMailExpediteur(),
            'mail_destinataire' => $ticket->getMailDestinataire(),
            'objet' => $ticket->getObjet(),
            'description' => $ticket->getDescription(),
            'date_creation' => $ticket->getDateCreation()->format('d-m-Y H:i'),
            'date_limite' => $ticket->getDateLimite()->format('d-m-Y'),
            'is_repondu' => $ticket->isRepondu(),
            'users' =>$userArray,
            'commentaires'=> $arrayComments,
            'salle' => $salleTicket
        );
        return new JsonResponse($arrayTickets);
    }

    #[OA\Delete(path: '/api/delete-ticket/{id}',
        description: "Cette route permet de supprimer de la base de donnée un ticket existant ",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id du ticket que nous voulons supprimer !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si la suppression du ticket a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si la suppression du ticket n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/delete-ticket/{id}', name: 'app_delete_ticket',methods: "delete")]
    public function delete($id):JsonResponse
    {
        try {
            $isJointeAlone = true;
            $deletedTicket = $this->em->getRepository(Ticket::class)->find($id);
            foreach ($deletedTicket->getUtilisateur() as $user){
                $deletedTicket->removeUtilisateur($user);
            }
            foreach ($deletedTicket->getCommentaire() as $comment){
                $deletedTicket->removeCommentaire($comment);
            }
            //On enlève les pieces jointes
            foreach ($deletedTicket->getPieceJointes() as $jointe){
                $ticketsJointe = $jointe->getTicket();
                $deletedTicket->removePieceJointe($jointe);
                //Et on les supprime si elles ne sont rattachées à aucun autre ticket
                foreach ($ticketsJointe as $ticket){
                    if ($ticket->getId() !== $id){
                        $isJointeAlone = false;
                    }
                }
                if($isJointeAlone){
                    $filesystem = new Filesystem();
                    $filesystem->remove( $jointe->getPath());
                    $this->em->remove($jointe);
                }
            }

            $this->em->remove($deletedTicket);
            $this->em->flush();
        }
        catch (Exception $e){
            return new JsonResponse($e->getMessage());
        }
        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }

    #[OA\Post(path: '/api/ticket-add-user/{idTicket}',
        description: "Cette route permet de taguer un ou plusieurs utilisateurs à un ticket enregistré en base de donnée",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "idTicket", description: "L'id du ticket sur lequel nous voulons tagué le(s) user(s) ", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès l'ajout de user(s) au ticket a eu lieu en base de données."),
            new OA\Response(response: "501", description: "Retourne un message d'erreur si il n'y a pas d'user dans le body"),
        ]
    )]
    #[Route('/api/ticket-add-user/{idTicket}', name: 'app_ticket_add_user',methods: "post")]
    public function addUserToTicket(int $idTicket,Request $request):JsonResponse
    {
        $users = $request->toArray();
        if($users == null){
            return new JsonResponse([
                'status' => 'error',
                'code' => '501',
                'message' => "users vide"
            ]);
        }
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($idTicket);
            //On enlève tout les users pour ne mettre que ceux que l'on demande
            //Sinon ceux si on veut détaguer (j'invente des verbes oui oui) quelqu'un bah il reste
            foreach ($ticket->getUtilisateur() as $ticketUser){
                $ticket->removeUtilisateur($ticketUser);
            }
            for($i = 0;$i<count($users);$i++){
                $user = $this->em->getRepository(User::class)->find($users[$i]['id']);
                $ticket->addUtilisateur($user);
            }
            $this->em->persist($ticket);
            $this->em->flush();
        }catch (Exception $exception){
            return $exception;
        }
        return new JsonResponse([
            "status" => "success",
            "code" => "200"
        ]);
    }

    #[OA\Get(path: '/api/ticket-user/{idUser}',
        description: "Cette route permet de récupérer tout les tickets dont le user passé en paramètre est tagué",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "idUser", description: "L'id du user dont nous voulons récupérer les tickets", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne le tickets au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/ticket-user/{idUser}', name: 'app_ticket_user')]
    public function getByUser(int $idUser):JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($idUser);
        $tickets = $this->em->getRepository(Ticket::class)->findAll();

        foreach ($tickets as $ticket){
            foreach ($ticket->getUtilisateur() as $ticketUser){
                if($ticketUser->getId() == $user->getId()){
                    $arrayComments = [];
                    $etat = $ticket->getEtat();
                    $salle = $ticket->getSalle();
                    $salleArray = array(
                        'id' => $salle->getId(),
                        'libelle' => $salle->getLibelle(),
                    );

                    $color = $etat->getColor();
                    list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                    if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                        $fontColor = "2F323A";
                    }else{
                        $fontColor = "ffffff";
                    }
                    $etatArray = array(
                        'id' => $etat->getId(),
                        'libelle' => $etat->getLibelle(),
                        'ordre' =>$etat->getOrdre(),
                        'color' => $etat->getColor(),
                        'fontColor' => $fontColor
                    );
                    if(count($ticket->getUtilisateur()) != 0){
                        $users = $ticket->getUtilisateur();
                        $color = $user->getColor();
                        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                            $fontColor = "2F323A";
                        }else{
                            $fontColor = "ffffff";
                        }
                        foreach ($users as $the_user){
                            $userArray[] = array(
                                'id' => $the_user->getId(),
                                'email' => $the_user->getEmail(),
                                'nom' =>$the_user->getNom(),
                                'prenom' =>$the_user->getPrenom(),
                                'color' => $the_user->getColor(),
                                'fontColor' => $fontColor
                            );
                        }
                    }
                    if(count($ticket->getCommentaire()) != 0){
                        $comments = $ticket->getCommentaire();
                        foreach ($comments as $comment){
                            $arrayComments[] = array(
                                'id' => $comment->getId(),
                                'contenu' =>$comment->getContenu(),
                                'created' =>$comment->getCreated()->format('d-m-Y H:i')
                            );
                        }
                    }
                    $arrayTicketUser[] = array(
                        'id' => $ticket->getId(),
                        'mail_expediteur' => $ticket->getMailExpediteur(),
                        'mail_destinataire' => $ticket->getMailDestinataire(),
                        'objet' => $ticket->getObjet(),
                        'description' => $ticket->getDescription(),
                        'date_creation' => $ticket->getDateCreation()->format('d-m-Y H:i'),
                        'date_limite' => $ticket->getDateLimite()->format('d-m-Y'),
                        'is_repondu' => $ticket->isRepondu(),
                        'etat' => $etatArray,
                        'salle' => $salleArray,
                        'users' => $userArray,
                        'commentaires' => $arrayComments
                    );
                }
            }
        }

        return new JsonResponse($arrayTicketUser);
    }

    #[OA\Post(path: '/api/add-commentaire/{ticketId}',
        description: "Cette route permet cette route permet d'ajouter un commentaire sur un ticket stocké en base de donnée dont l'id est passé en paramètre.",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "ticketId", description: "L'id du ticket sur lequel nous voulons ajouter un commentaire.", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne le tickets sur lequel nous vennons d'ajouter un commentaire au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/add-commentaire/{ticketId}', name: 'app_commentaire_to_ticket',methods: "post")]
    public function addCommentaire(int $ticketId,Request $request): JsonResponse
    {
        date_default_timezone_set('Europe/Paris');
        $data = $request->toArray();
        $user = $this->em->getRepository(User::class)->find($data['user']['id']);
        $ticket = $this->em->getRepository(Ticket::class)->find($ticketId);

        $color = $user->getColor();
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
            $fontColor = "2F323A";
        }else{
            $fontColor = "ffffff";
        }
        $userArray = array(
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'color' => $user->getColor(),
            'fontColor' => $fontColor
        );


        $commenatire = new Commentaire();
        $commenatire->setContenu($data['commentaire']['contenu']);
        $commenatire->setUtilisateur($user);
        $commenatire->setTicket($ticket);
        $now = new \DateTime('now');
        $commenatire->setCreated($now);

        $this->em->persist($commenatire);
        $this->em->flush();

        $commenatireArray = array(
            'id' => $commenatire->getId(),
            'contenu' => $commenatire->getContenu(),
            'created' => $commenatire->getCreated()->format('d-m-Y H:i'),
            'user' => $userArray,
        );

        return new JsonResponse($commenatireArray);
    }

    #[OA\Put(path: '/ticket-change/{ticketId}/etat/{etatId}',
        description: "Cette route permet de changer l'état d'un ticket stocké en base de donnée dont les id sont passés en paramètre.",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "ticketId", description: "L'id du ticket dont nous voulons changer l'état", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
            new OA\Parameter(name: "etatId", description: "L'id de l'état que nous voulons assigné au ticket", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si le changement d'état a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si le changement d'état n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/ticket-change/{ticketId}/etat/{etatId}', name: 'app_change_etat_ticket',methods: "put")]
    public function changeEtat(int $ticketId,int $etatId){
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($ticketId);
            $etat = $this->em->getRepository(Etat::class)->find($etatId);
            $ticket->setEtat($etat);
            $this->em->persist($ticket);
            $this->em->flush();
        }catch (Exception $e){
            return new JsonResponse($e);
        }

        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }

    #[OA\Put(path: '/ticket-change/{ticketId}/salle/{salleId}',
        description: "Cette route permet de changer la salle d'un ticket stocké en base de donnée dont les id sont passés en paramètre.",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "ticketId", description: "L'id du ticket dont nous voulons changer la salle", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
            new OA\Parameter(name: "salleId", description: "L'id de la salle que nous voulons assigné au ticket", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si le changement de salle a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si le changement de salle n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/ticket-change/{ticketId}/salle/{salleId}', name: 'app_change_salle_ticket',methods: "put")]
    public function changeSalle(int $ticketId,int $salleId){
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($ticketId);
            $salle = $this->em->getRepository(Salle::class)->find($salleId);
            $ticket->setSalle($salle);
            $this->em->persist($ticket);
            $this->em->flush();
        }catch (Exception $e){
            return new JsonResponse($e);
        }

        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }

    #[OA\Put(path: '/ticket-change-repondu/{ticketId}',
        description: "Cette route permet de changer l'attribut répondu d'un ticket stocké en base de donnée dont les id sont passés en paramètre.",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "ticketId", description: "L'id du ticket dont nous voulons changer l'attribut répondu", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si le changement eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si le changement n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/ticket-change-repondu/{ticketId}', name: 'app_change_repondu_ticket',methods: "put")]
    public function changeRepondu(int $ticketId){
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($ticketId);
            $ticket->setRepondu(!$ticket->isRepondu());
            $this->em->persist($ticket);
            $this->em->flush();
        }catch (Exception $e){
            return new JsonResponse($e);
        }

        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }

    #[OA\Get(path: '/tickets-date-limite',
        description: "Cette route permet de récupérer les tickets triés par date limite",
        tags: ["Ticket"],
        responses: [
            new OA\Response(response: "200", description: "Retourne les tickets triés par date limite au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/tickets-date-limite', name: 'app_change_date_limite_ticket',methods: "get")]
    public function ticketByDateLimite(){
        $tickets = $this->em->getRepository(Ticket::class)->getTicketsWithoutEtat();
        foreach ($tickets as $ticket) {
            $userArray = [];
            $arrayComments = [];
            $commentUser = [];
            $etat = $ticket->getEtat();
            $salle = $ticket->getSalle();

            $color = $etat->getColor();
            list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
            if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                $fontColor = "2F323A";
            }else{
                $fontColor = "ffffff";
            }
            $etatArray = array(
                'id' => $etat->getId(),
                'libelle' => $etat->getLibelle(),
                'ordre' =>$etat->getOrdre(),
                'color' => $etat->getColor(),
                'fontColor' => $fontColor
            );
            $salleArray = array(
                'id' => $salle->getId(),
                'libelle' => $salle->getLibelle(),
            );
            //On récup les users taggué sur le ticket si il y en a
            if(count($ticket->getUtilisateur()) != 0){
                $users = $ticket->getUtilisateur();
                foreach ($users as $user){
                    $color = $user->getColor();
                    list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                    if(($r*0.299 + $g*0.587 + $b*0.114) > 186){
                        $fontColor = "2F323A";
                    }else{
                        $fontColor = "ffffff";
                    }
                    $userArray[] = array(
                        'id' => $user->getId(),
                        'email' => $user->getEmail(),
                        'nom' =>$user->getNom(),
                        'prenom' =>$user->getPrenom(),
                        'color' => $user->getColor(),
                        'fontColor' => $fontColor
                    );
                }
            }
            //Pareil avec les commentaires
            if(count($ticket->getCommentaire()) != 0){
                $comments = $ticket->getCommentaire();
                foreach ($comments as $comment){
                    //On charge l'utilisateur dont provient le commentaire
                    $commentUser = array(
                        'id' => $comment->getUtilisateur()->getId(),
                        'email' => $comment->getUtilisateur()->getEmail(),
                        'nom' =>$comment->getUtilisateur()->getNom(),
                        'prenom' =>$comment->getUtilisateur()->getPrenom(),
                    );


                    $arrayComments[] = array(
                        'id' => $comment->getId(),
                        'contenu' =>$comment->getContenu(),
                        'created' =>$comment->getCreated()->format('d-m-Y H:i'),
                        'user' => $commentUser
                    );
                }
            }
            $arrayTickets[] = array(
                'id' => $ticket->getId(),
                'mail_expediteur' => $ticket->getMailExpediteur(),
                'mail_destinataire' => $ticket->getMailDestinataire(),
                'objet' => $ticket->getObjet(),
                'description' => $ticket->getDescription(),
                'date_creation' => $ticket->getDateCreation()->format('Y-m-d H:i'),
                'date_limite' => $ticket->getDateLimite()->format('Y-m-d'),
                'is_repondu' => $ticket->isRepondu(),
                'etat' => $etatArray,
                'salle' => $salleArray,
                'users' => $userArray,
                'commentaires' => $arrayComments
            );
        }
        return new JsonResponse($arrayTickets);
    }

    #[OA\Get(path: '/tickets-date-limite',
        description: "Cette route permet de récupérer uniquement les tickets qui ont l'état terminé",
        tags: ["Ticket"],
        responses: [
            new OA\Response(response: "200", description: "Retourne les tickets triés par date limite au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Ticket"),
                )
            ),
        ]
    )]
    #[Route('/api/tickets-termine', name: 'app_ticket_termine',methods: "get")]
    public function ticketByEtatTermine(){
        try {
            $tickets = $this->em->getRepository(Ticket::class)->getTicketsByEtatTerminer();
            foreach ($tickets as $ticket) {
                $userArray = [];
                $arrayComments = [];
                $commentUser = [];
                $etat = $ticket->getEtat();
                $salle = $ticket->getSalle();

                $color = $etat->getColor();
                list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                    $fontColor = "2F323A";
                }else{
                    $fontColor = "ffffff";
                }
                $etatArray = array(
                    'id' => $etat->getId(),
                    'libelle' => $etat->getLibelle(),
                    'ordre' =>$etat->getOrdre(),
                    'color' => $etat->getColor(),
                    'fontColor' => $fontColor
                );
                $salleArray = array(
                    'id' => $salle->getId(),
                    'libelle' => $salle->getLibelle(),
                );
                //On récup les users taggué sur le ticket si il y en a
                if(count($ticket->getUtilisateur()) != 0){
                    $users = $ticket->getUtilisateur();
                    foreach ($users as $user){
                        $color = $user->getColor();
                        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
                        if(($r*0.299 + $g*0.587 + $b*0.114) > 186){
                            $fontColor = "2F323A";
                        }else{
                            $fontColor = "ffffff";
                        }
                        $userArray[] = array(
                            'id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'nom' =>$user->getNom(),
                            'prenom' =>$user->getPrenom(),
                            'color' => $user->getColor(),
                            'fontColor' => $fontColor
                        );
                    }
                }
                //Pareil avec les commentaires
                if(count($ticket->getCommentaire()) != 0){
                    $comments = $ticket->getCommentaire();
                    foreach ($comments as $comment){
                        //On charge l'utilisateur dont provient le commentaire
                        $commentUser = array(
                            'id' => $comment->getUtilisateur()->getId(),
                            'email' => $comment->getUtilisateur()->getEmail(),
                            'nom' =>$comment->getUtilisateur()->getNom(),
                            'prenom' =>$comment->getUtilisateur()->getPrenom(),
                        );


                        $arrayComments[] = array(
                            'id' => $comment->getId(),
                            'contenu' =>$comment->getContenu(),
                            'created' =>$comment->getCreated()->format('d-m-Y H:i'),
                            'user' => $commentUser
                        );
                    }
                }
                $arrayTickets[] = array(
                    'id' => $ticket->getId(),
                    'mail_expediteur' => $ticket->getMailExpediteur(),
                    'mail_destinataire' => $ticket->getMailDestinataire(),
                    'objet' => $ticket->getObjet(),
                    'description' => $ticket->getDescription(),
                    'date_creation' => $ticket->getDateCreation()->format('d-m-Y H:i'),
                    'date_limite' => $ticket->getDateLimite()->format('d-m-Y'),
                    'is_repondu' => $ticket->isRepondu(),
                    'etat' => $etatArray,
                    'salle' => $salleArray,
                    'users' => $userArray,
                    'commentaires' => $arrayComments
                );
            }
            return new JsonResponse($arrayTickets);
        }catch (Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'Pas de ticket terminé'
            ]);
        }
    }
#[OA\Put(path: '/add-user/{idUser}/ticket/{idTicket}',
        description: "Cette route permet d'ajouter un utilisateur à un ticket",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "idUser", description: "L'id de l'utilisateur à ajouter au ticket", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
            new OA\Parameter(name: "idTicket", description: "L'id du ticket auquel on veut ajouter l'utilisateur", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si l'ajout a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si l'ajout n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/add-user/{idUser}/ticket/{idTicket}', name: 'app_add_user_ticket',methods: "put")]
    public function addUserTicket(int $idUser,int $idTicket){
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($idTicket);
            $user = $this->em->getRepository(User::class)->find($idUser);
            $users = $ticket->getUtilisateur();
            $userDejaTag = false;
            foreach($users as $userTicket){
                if($userTicket->getId() == $idUser){
                    $userDejaTag = true;
                }
            }
            if(!$userDejaTag){
                $ticket->addUtilisateur($user);
                $this->em->persist($ticket);
                $this->em->flush();

                return new JsonResponse([
                    'status' => 'success',
                    'code' => '200'
                ]);
            }else{
                return new JsonResponse([
                    'status' => 'error',
                    'code' => '501',
                    'message' => 'L\'utilisateur est déjà taggué sur ce ticket'
                ]);
            }
        }catch (Exception $e){
            return new JsonResponse($e);
        }

    }

    #[OA\Delete(path: '/delete-user/{idUser}/ticket/{idTicket}',
        description: "Cette route permet de supprimer un utilisateur d'un ticket",
        tags: ["Ticket"],
        parameters: [
            new OA\Parameter(name: "idUser", description: "L'id de l'utilisateur à supprimer du ticket", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
            new OA\Parameter(name: "idTicket", description: "L'id du ticket duquel on veut supprimer l'utilisateur", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si la suppression a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si la suppression n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/delete-user/{idUser}/ticket/{idTicket}', name: 'app_delete_user_ticket',methods: "delete")]
    public function deleteUserTicket(int $idUser,int $idTicket){
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($idTicket);
            $user = $this->em->getRepository(User::class)->find($idUser);
            $ticket->getUtilisateur()->removeElement($user);
            $this->em->persist($ticket);
            $this->em->flush();
        }catch (Exception $e){
            return new JsonResponse($e);
        }
        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
        return new JsonResponse($arrayTickets);
    }


}
