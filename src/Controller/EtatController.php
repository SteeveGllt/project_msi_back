<?php

namespace App\Controller;

use App\Entity\Ticket;
use OpenApi\Attributes as OA;
use App\Entity\Etat;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EtatController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    #[OA\Get(path: '/api/etats',
        description: "Cette route permet de retourner tous les états présentes en base de données au format Json.",
        tags: ["Etat"],
        responses: [
            new OA\Response(response: "200", description: "Retourne tous les états au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Etat"),
                )
            ),
        ]
    )]
    #[Route('/api/etats', name: 'app_etat')]
    public function index(): JsonResponse
    {
        $etats = $this->em->getRepository(Etat::class)->findAll();

        $arrayEtats = array();
        foreach ($etats as $etat){
            $color = $etat->getColor();
            list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
            if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                $fontColor = "2F323A";
            }else{
                $fontColor = "ffffff";
            }
            $arrayEtats[] = array(
                'id' => $etat->getId(),
                'libelle' => $etat->getLibelle(),
                'ordre' => $etat->getOrdre(),
                'color' => $etat->getColor(),
                'fontColor' => $fontColor,
            );
        }
        return new JsonResponse($arrayEtats);
    }

    #[OA\Get(path: '/api/etat/{id}',
        description: "Cette route permet de retourner l'état dont l'id est passé en paramètre au format Json.",
        tags: ["Etat"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id de la l'état que nous voulons récupéré !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne l'état dont l'id est passé en paramètre au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Etat"),
                )
            ),
        ]
    )]
    #[Route('/api/etat/{id}', name: 'app_one_etat')]
    public function indexOne(int $id):JsonResponse
    {
        $etat = $this->em->getRepository(Etat::class)->find($id);
        $color = $etat->getColor();
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
            $fontColor = "2F323A";
        }else{
            $fontColor = "ffffff";
        }
        $arrayEtats = array(
            'id' => $etat->getId(),
            'libelle' => $etat->getLibelle(),
            'ordre' => $etat->getOrdre(),
            'color' => $etat->getColor(),
            'fontColor' => $fontColor,
        );
        return new JsonResponse($arrayEtats);
    }

    #[OA\Post(path: '/api/create-etat',
        description: "Cette route permet de créer un état en base de donnée.",
        tags: ["Etat"],
        responses: [
            new OA\Response(response: "200", description: "Retourne au format Json l'état qui vient d'être créé et enregistrer en base de données.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Etat"),
                )
            ),
        ]
    )]
    #[Route('/api/create-etat', name: 'app_create_etat',methods: "post")]
    public function create(Request $request):JsonResponse
    {
        $data = $request->toArray();

        $etat = new Etat();


        if($data["libelle"] === "Terminé"){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "On touche pas à l'état terminé"
            ]);
        }else{
            $etat->setLibelle($data["libelle"]);
        }
        $etat->setOrdre($data["ordre"]);
        $etat->setColor($data["color"]);
        $this->em->persist($etat);
        $this->em->flush();

        $color = $etat->getColor();
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
            $fontColor = "2F323A";
        }else{
            $fontColor = "ffffff";
        }

        $arrayEtats = array(
            'id' => $etat->getId(),
            'libelle' => $etat->getLibelle(),
            'ordre' => $etat->getOrdre(),
            'color' => $etat->getColor(),
            'fontColor' => $fontColor,
        );
        return new JsonResponse($arrayEtats);
    }

    #[OA\Put(path: '/api/edit-etat/{id}',
        description: "Cette route permet de modifier un état déjà existant en base de donnée.",
        tags: ["Etat"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id de la l'état que nous voulons modifier !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne au format Json l'état qui vient d'être modifié et enregistrer en base de données.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Etat"),
                )
            ),
        ]
    )]
    #[Route('/api/edit-etat/{id}', name: 'app_edit_etat',methods: "put|patch")]
    public function edit(int $id,Request $request):JsonResponse
    {
        $data = $request->toArray();

        $etat = $this->em->getRepository(Etat::class)->find($id);
        if($data["libelle"] === "Terminé" && $etat->getLibelle() !== "Terminé"){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "On ne renomme pas un état en Terminé"
            ]);
        }
        if($etat->getLibelle() === "Terminé" && $etat->getLibelle() !== $data["libelle"]){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "On ne renomme pas l'état Terminé"
            ]);
        }else{
            $etat->setLibelle($data["libelle"]);
        }
        $etat->setOrdre($data["ordre"]);
        $etat->setColor($data["color"]);
        $this->em->persist($etat);
        $this->em->flush();

        $color = $etat->getColor();
        list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
        if($r*0.299 + $g*0.587 + $b*0.114 > 186){
            $fontColor = "2F323A";
        }else{
            $fontColor = "ffffff";
        }
        $arrayEtats = array(
            'id' => $etat->getId(),
            'libelle' => $etat->getLibelle(),
            'ordre' => $etat->getOrdre(),
            'color' => $etat->getColor(),
            'fontColor' => $fontColor,
        );
        return new JsonResponse($arrayEtats);
    }

    #[OA\Delete(path: '/api/delete-etat/{id}',
        description: "Cette route permet de supprimer de la base de donnée un état existant ",
        tags: ["Etat"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id de la l'état que nous voulons supprimer !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si la suppression de l'état a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si la suppression de l'état n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/delete-etat/{id}', name: 'app_delete_etat',methods: "delete")]
    public function delete(int $id):JsonResponse
    {
        try{
            $etat = $this->em->getRepository(Etat::class)->find($id);
            if($etat->getLibelle() === "Terminé"){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => "On ne supprime pas l'état Terminé"
                ]);
            }else{
                $this->em->remove($etat);
                $this->em->flush();
            }
        }catch (\Exception $e){
            return new JsonResponse($e);
        }

        return new JsonResponse([
            'status' => 'success',
            'code'=>'200'
        ]);
    }

    #[OA\Get(path: '/api/create-etat',
        description: "Cette route permet de récupéré l'état d'un ticket dont l'id est passé en paramètre.",
        tags: ["Etat"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id du ticket dont nous voulons récupéré l'état", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne au format Json l'état qui vient d'être créé et enregistrer en base de données.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Etat"),
                )
            ),
        ]
    )]
    #[Route('/api/get-etat-ticket/{idTicket}', name: 'app_get_etat_ticket',methods: "get")]
    public function getFromTicket(int $idTicket):JsonResponse
    {
        try{
            $ticket = $this->em->getRepository(Ticket::class)->find($idTicket);
            $etat = $ticket->getEtat();
            $color = $etat->getColor();
            list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
            if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                $fontColor = "2F323A";
            }else{
                $fontColor = "ffffff";
            }
            $arrayEtats = array(
                'id' => $etat->getId(),
                'libelle' => $etat->getLibelle(),
                'ordre' => $etat->getOrdre(),
                'color' => $etat->getColor(),
                'fontColor' => $fontColor,
            );
            return new JsonResponse($arrayEtats);
        }catch (\Exception $e){
            return new JsonResponse($e);
        }
    }

}
