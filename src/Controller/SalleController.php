<?php

namespace App\Controller;

use App\Entity\Salle;
use League\Csv\Reader;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SalleController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    #[OA\Get(path: '/api/salles',
        description: "Cette route permet de retourner toutes les salles présentes en base de données au format Json.",
        tags: ["Salle"],
        responses: [
            new OA\Response(response: "200", description: "Retourne toutes les salles au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Salle"),
                )
            ),
        ]
    )]
    #[Route('/api/salles', name: 'app_salle', methods: "get" )]
    public function index(): JsonResponse
    {
        $salles = $this->em->getRepository(Salle::class)->findAll();
        $arraySalles = array();
        foreach ($salles as $salle){
            $arraySalles[] = array(
                'id' => $salle->getId(),
                'libelle' => $salle->getLibelle(),
            );
        }
        return new JsonResponse($arraySalles);
    }

    #[OA\Get(path: '/api/salle/{id}',
        description: "Cette route permet de retourner une salle en fonction de son id au format Json.",
        tags: ["Salle"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id de la salle que nous voulons récupérer !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne une salle au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Salle"),
                )
            ),
        ]
    )]
    #[Route('/api/salle/{id}', name: 'app_salle_one', methods: "get")]
    public function indexOne(int $id): JsonResponse
    {
        $salle = $this->em->getRepository(Salle::class)->find($id);
            $arraySalles[] = array(
                'id' => $salle->getId(),
                'libelle' => $salle->getLibelle(),
            );
        return new JsonResponse($arraySalles);
    }

    #[OA\Post(path: '/api/create-salle',
        description: "Cette route permet de créer une salle en base de données.",
        tags: ["Salle"],
        /*requestBody: new OA\RequestBody(description: "Données de la salle à créer", required: true, content:
            new OA\MediaType(mediaType: "application/json", schema:
                new OA\Schema(type: "object", properties:
                    new OA\Property(property: "libelle", type: "string"),
                )
            )
        ),*/
        responses: [
            new OA\Response(response: "200", description: "Retourne la salle créée au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Salle"),
                )
            ),
        ]
    )]
    #[Route('/api/create-salle', name: 'app_salle_create', methods: "post")]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $salle = new Salle();
        $salle->setLibelle($data['libelle']);
        $this->em->persist($salle);
        $this->em->flush();
        $arraySalles[] = array(
            'id' => $salle->getId(),
            'libelle' => $salle->getLibelle(),
        );

        return new JsonResponse($arraySalles);
    }

    #[OA\Put(path: '/api/edit-salle/{id}',
        description: "Cette route permet de modifier une salle en base de données.",
        tags: ["Salle"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id de la salle que nous voulons modifier !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        /*requestBody: new OA\RequestBody(description: "Données de la salle à modifier", required: true, content:
            new OA\MediaType(mediaType: "application/json", schema:
                new OA\Schema(type: "object", properties:
                    new OA\Property(property: "libelle", type: "string"),
                )
            )
        ),*/
        responses: [
            new OA\Response(response: "200", description: "Retourne la salle modifiée au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/Salle"),
                )
            ),
        ]
    )]
    #[Route('/api/edit-salle/{id}', name: 'app_salle_edit', methods: "put")]
    public function edit(Request $request,int $id): JsonResponse
    {
        $data = $request->toArray();
        $salle = $this->em->getRepository(Salle::class)->find($id);
        $salle->setLibelle($data['libelle']);
        $this->em->persist($salle);
        $this->em->flush();
        $arraySalles[] = array(
            'id' => $salle->getId(),
            'libelle' => $salle->getLibelle(),
        );

        return new JsonResponse($arraySalles);
    }

    #[OA\Delete(path: '/api/delete-salle/{id}',
        description: "Cette route permet de supprimer une salle en base de données.",
        tags: ["Salle"],
        parameters: [
            new OA\Parameter(name: "id", description: "L'id de la salle que nous voulons supprimer !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si la suppression du ticket a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si la suppression du ticket n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/delete-salle/{id}', name: 'app_salle_delete', methods: "delete")]
    public function delete(int $id): JsonResponse
    {

        try {
            $salle = $this->em->getRepository(Salle::class)->find($id);
            $this->em->remove($salle);
            $this->em->flush();
        } catch (\Exception $e){
            return new JsonResponse($e);
        }
        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }

    #[Route('/api/import-csv', name: 'app_import_csv', methods: "POST")]
    public function importCsv(Request $request) : JsonResponse
    {
        try {
            $file = $request->files->get('file');
            $csvReader = Reader::createFromPath($file->getPathname());
            foreach ($csvReader as $row){
                $salle = new Salle();
                $salle->setLibelle($row[0]);
                $this->em->persist($salle);
            }
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
