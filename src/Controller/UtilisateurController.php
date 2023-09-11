<?php

namespace App\Controller;

use App\Entity\User;
use OpenApi\Attributes as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    private EntityManagerInterface $em;
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $encoder)
    {
        $this->em = $entityManager;
        $this->encoder = $encoder;
    }


    #[OA\Get(path: '/api/utilisateurs',
        description: "Cette route permet de retourner tous les utilisateurs présentes en base de données au format Json.",
        tags: ["User"],
        responses: [
            new OA\Response(response: "200", description: "Retourne tous les utilisateurs au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/User"),
                )
            ),
        ]
    )]
    #[Route('/api/utilisateurs', name: 'app_utilisateur')]
    public function index(): JsonResponse
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $userArray = array();

        foreach ($users as $user) {
            $color = $user->getColor();
            list($r, $g, $b) = sscanf($color, "%02x%02x%02x");
            if($r*0.299 + $g*0.587 + $b*0.114 > 186){
                $fontColor = "000000";
            }else{
                $fontColor = "ffffff";
            }
            $userArray[] = array(
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                'color' => $user->getColor(),
                'fontColor' => $fontColor,
            );
        }

        return new JsonResponse($userArray);
    }

    #[OA\Get(path: '/api/utilisateur/{email}',
        description: "Cette route permet de retourner un utilisateur en fonction de son email au format Json.",
        tags: ["User"],
        parameters: [
            new OA\Parameter(name: "email", description: "L'email de l'utilisateur que nous voulons récupérer !", in: "path", required: true, schema:
                new OA\Schema(type: "integer"),
            ),
        ],
        responses: [
            new OA\Response(response: "200", description: "Retourne un message de succès si la suppression du ticket a eu lieu en base de données."),
            new OA\Response(response: "500", description: "Retourne un message d'erreur si la suppression du ticket n'a pas eu lieu en base de données."),
        ]
    )]
    #[Route('/api/utilisateur/{email}', name: 'app_utilisateur_by_email')]
    public function indexByMail(string $email): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
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
            'fontColor' => $fontColor,
        );

        return new JsonResponse($userArray);
    }

    #[Route('/api/utilisateur-info/{id}', name: 'app_utilisateur_by_id')]
    public function indexOne(int $id): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);
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
            'fontColor' => $fontColor,
        );

        return new JsonResponse($userArray);
    }

    #[OA\Post(path: '/api/create-utilisateur',
        description: "Cette route permet de créer un utilisateur en base de données.",
        tags: ["User"],
        responses: [
            new OA\Response(response: "200", description: "Retourne l'utilisateur créé au format JSON.", content:
                new OA\JsonContent(type: "array", items:
                    new OA\Items(ref: "#/components/schemas/User"),
                )
            ),
        ]
    )]
    #[Route('/api/create-utilisateur', name: 'app_utilisateur_create', methods: "post")]
    public function create(Request $request): JsonResponse
    {
        $data = $request->toArray();
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(array('ROLE_ADMIN'));
        $encoded = $this->encoder->hashPassword($user, $data['password']);
        $user->setPassword($encoded);//password
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setColor($data['color']);
        $this->em->persist($user);
        $this->em->flush();
        $userArray = array(
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'color' => $user->getColor(),
        );

        return new JsonResponse($userArray);
    }

    #[Route('/api/edit-utilisateur/{id}', name: 'app_utilisateur_edit', methods: "put")]
    public function edit(Request $request,int $id): JsonResponse
    {
        $data = $request->toArray();
        $user = $this->em->getRepository(User::class)->find($id);
        $user->setEmail($data['email']);
        $user->setNom($data['nom']);
        $user->setPrenom($data['prenom']);
        $user->setColor($data['color']);
        $this->em->persist($user);
        $this->em->flush();
        $arrayUsers[] = array(
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'nom' => $user->getNom(),
            'prenom' => $user->getPrenom(),
            'color' => $user->getColor(),
        );

        return new JsonResponse($arrayUsers);
    }
    #[Route('/api/delete-utilisateur/{id}', name: 'app_utilisateur_delete', methods: "delete")]
    public function delete(int $id): JsonResponse
    {
        try {
            $user = $this->em->getRepository(User::class)->find($id);
            $this->em->remove($user);
            $this->em->flush();
        } catch (\Exception $e){
            return new JsonResponse($e);
        }
        return new JsonResponse([
            'status' => 'success',
            'code' => '200'
        ]);
    }

    #[Route('/api/change-password/{id}', name: 'app_utilisateur_change_password', methods: "put")]
    public function changePassword(int $id,Request $request): JsonResponse
    {
        $data = $request->toArray();
        $user = $this->em->getRepository(User::class)->find($id);
        if($this->encoder->isPasswordValid($user,$data["password"])){
            if($data["newPassword"] === $data["newPasswordConfirm"]){
                try{
                    $encoded = $this->encoder->hashPassword($user,$data["newPassword"]);
                    $user->setPassword($encoded);
                    $this->em->persist($user);
                    $this->em->flush();
                }catch (\Exception $e){
                    return new JsonResponse([
                        'status' => 'error',
                        'code' => 500,
                        'message' => $e->getMessage()
                    ]);
                }
            }else{
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => 'Les mots de passe sont différents'
                ]);
            }
        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => 'Mauvais mot de passe'
            ]);
        }
        return new JsonResponse([
            'status' => 'success',
            'code' => 200,
            'message' => 'Le mot de pass a bien été changé'
        ]);
    }
}
