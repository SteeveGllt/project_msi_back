<?php

namespace App\Controller;

use App\Entity\PieceJointe;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class PieceJointeController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    #[OA\Get(path: '/api/nb-piece-jointe/ticket/{ticketId}',
        description: "Cette route permet de récupérer le nombre de pieces jointes d'un ticket dont l'id a été passé en paramètre",
        tags: ["PieceJointe"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id du ticket que nous voulons modifier !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne au format Json l'état qui vient d'être modifié et enregistrer en base de données.", content:
                new OA\JsonContent(type: "int", items:
                    new OA\Items(ref: "#/components/schemas/PieceJointe"),
                )
            ),
            new OA\Response(response: "500", description: "Retourne au format Json une erreur ainsi que son message"),
        ]
    )]
    #[Route('/api/nb-piece-jointe/ticket/{ticketId}', name: 'app_piece_jointe')]
    public function getNbPieceJointe($ticketId): JsonResponse
    {
        $ticket = $this->em->getRepository(Ticket::class)->find($ticketId);

        $nbPieceJointe = 0;
        $piecesJointesTicket = $ticket->getPieceJointes();
        foreach ($piecesJointesTicket as $pieceJointeTicket){
            $nbPieceJointe++;
        }

        return new JsonResponse($nbPieceJointe);
    }

    #[Route('/api/piece-jointe/ticket/{ticketId}', name: 'app_get_jointe_ticket')]
    public function getPieceJointe($ticketId): JsonResponse
    {
        $ticket = $this->em->getRepository(Ticket::class)->find($ticketId);
        $pieceJointeArray = array();
        $piecesJointesTicket = $ticket->getPieceJointes();
        foreach ($piecesJointesTicket as $pieceJointeTicket){
            $pieceJointeArray[] = array(
                'id'=> $pieceJointeTicket->getId(),
                'path'=> $pieceJointeTicket->getPath(),
            );
        }

        return new JsonResponse($pieceJointeArray);
    }
}
