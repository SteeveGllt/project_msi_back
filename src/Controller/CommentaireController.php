<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\PieceJointe;
use App\Entity\Salle;
use App\Entity\Ticket;
use mysql_xdevapi\Exception;
use OpenApi\Attributes as OA;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Expr\Array_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File as PhpFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use \GuzzleHttp;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

class CommentaireController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    #[OA\Get(path: '/api/commentaire',
        description: "Cette route permet de retourner tous les commenataires stocké en base de donnée.",
        tags: ["Commentaire"],
        responses: [
            new OA\Response(response: "200", description: "Retourne tous les commenataires sur un ticket stocké en base de donnée.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Commentaire"),
                )
            ),
        ]
    )]
    #[Route('/api/commentaires', name: 'app_commentaire_ticket',methods: "get")]
    public function index(): JsonResponse
    {
        $commentaires = $this->em->getRepository(Commentaire::class)->findAll();
        $commentairesArray[] = array();
        foreach ($commentaires as $commentaire){
            $commentairesArray[] = array(
                'id' => $commentaire->getId(),
                'contenu' => $commentaire->getContenu(),
                'created' => $commentaire->getCreated(),
                'ticket_id' => $commentaire->getTicket()->getId(),
                'utilisateur_id' => $commentaire->getUtilisateur()->getId(),
            );
        }

        return new JsonResponse($commentairesArray);
    }

    #[OA\Delete(path: '/api/commentaire',
        description: "Cette route permet de retourner tous les commenataires stocké en base de donnée.",
        tags: ["Commentaire"],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si la suppression du commentaire a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si la suppression du commentaire n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/delete-commentaire/{id}', name: 'app_commentaire_delete', methods: "delete")]
    public function delete(int $id): JsonResponse
    {
        try {
            $commentaire = $this->em->getRepository(Commentaire::class)->find($id);
            $this->em->remove($commentaire);
            $this->em->flush();
        } catch (\Exception $e){
            return new JsonResponse($e);
        }
        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }
}
