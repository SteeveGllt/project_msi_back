<?php

namespace App\Controller;

use App\Entity\RecupPassword;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Config\Doctrine\Orm\EntityManagerConfig\SecondLevelCache\RegionCacheDriverConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class RecupPasswordController extends AbstractController
{
    private EntityManagerInterface $em ;
    private $env;
    private MailerInterface $mailer;
    private $encoder;

    public function __construct(EntityManagerInterface $entityManager,KernelInterface $kernel,MailerInterface $mailerNew,UserPasswordHasherInterface $encoder) {
        $this->em = $entityManager;
        $this->env = $kernel;
        $this->mailer = $mailerNew;
        $this->encoder = $encoder;
    }

    private static function generateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $max)];
        }

        return $randomString;
    }

    #[Route('/api/get-autorization', name: 'app_get_all_token', methods: "post")]
    public function index(\Symfony\Component\HttpFoundation\Request $request): JsonResponse
    {
        date_default_timezone_set('Europe/Paris');
        $data = $request->toArray();
        $autorize = "";
        $date_now = new \DateTime();
        $recupPasswords = $this->em->getRepository(RecupPassword::class)->findAll();
        foreach ($recupPasswords as $recupPassword){
            $date_limite_str = explode("|", $data["token"]);
            $date_limite = new \DateTime($date_limite_str[1]);
            if($data["token"] === $recupPassword->getToken() && $date_now <= $date_limite ){
                return new JsonResponse(['autorization' => true,'user_id' => $recupPassword->getUser()->getId()]);
            }
        }
        return new JsonResponse(['autorization' => false]);
    }

    #[Route('/api/recup-password/{id}', name: 'app_forget_password',methods: "put")]
    public function changePassword(\Symfony\Component\HttpFoundation\Request $request,$id): JsonResponse
    {
        $data = $request->toArray();
        $user = $this->em->getRepository(User::class)->find($id);
        if($data["password"] === $data["passwordConfirm"]){
            try{
                $user->setPassword($this->encoder->hashPassword($user,$data["password"]));
                $this->em->persist($user);
                $recupPassword = $user->getRecupPassword();
                $user2 = new User();
                $user2->setEmail("");
                $user2->setPassword("");
                $user2->setPrenom("");
                $user2->setNom("");
                $user2->setColor("");
                $user2->setRoles([""]);
                $recupPassword->setUser($user2);
                $this->em->remove($recupPassword);
                $this->em->flush();
            }catch (\Exception $exception){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 500,
                    'message' => "Une erreur est survenue veuillez reesayer plus tard.",
                    'errorMessage' => $exception->getMessage()
                ]);
            }
            return new JsonResponse([
                'status' => 'success',
                'code' => 200,
                'message' => "Le mot de passe à été changé avec succès !"
            ]);
        }else{
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "Les mots de passe ne correspondent pas."
            ]);
        }
    }

    #[Route('/api/generate-token/{email}', name: 'app_generate_token',methods:"post")]
    public function generateToken($email,JWTTokenManagerInterface $JWTTokenManager): JsonResponse
    {
        date_default_timezone_set('Europe/Paris');
        try {
            $user = $this->em->getRepository(User::class)->findOneBy(["email" => $email]);
            if($user === null){
                return new JsonResponse([
                    'status' => 'error',
                    'code' => 501,
                    'message' => "Le mail n'existe pas."
                ]);
            }
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => $e->getMessage()
            ]);
        }

        if($user->getRecupPassword() !== null) {
            $recup = $user->getRecupPassword();
            $user2 = new User();
            $user2->setEmail("");
            $user2->setPassword("");
            $user2->setPrenom("");
            $user2->setNom("");
            $user2->setColor("");
            $user2->setRoles([""]);
            $recup->setUser($user2);
            $this->em->remove($recup);
            $this->em->flush();
        }
        $randomString = $this->generateRandomString(70);

        $currentDateTime = new \DateTime();
        $currentDateTime->add(new \DateInterval('PT10M'));

        $formattedDateTime = $currentDateTime->format('Y-m-d-H:i:s');

        $token = $randomString ."|". $formattedDateTime;
        $recupPassword = new RecupPassword();
        $recupPassword->setToken($token);
        $recupPassword->setUser($user);
        $this->em->persist($recupPassword);
        $this->em->flush();

        if($this->getParameter('kernel.environment')  === "dev"){
            $url = "http://localhost:5173/recup-password/".$token;
        }else{
            $url = "http://msi.groupemontroland.fr/recup-password/".$token;
        }

        $mailDestinataire = $user->getEmail();
        $mail = (new Email())
            ->from('serviceinformatique@groupemontroland.fr')
            ->to($mailDestinataire)
            ->subject("Récupération de mot de passe")
            ->html('
                        <!DOCTYPE html>
                        <html lang="fr"> 
                        <head> 
                        </head>
                        <body> 
                        <div> <p>Clic là</p>
                        <a href='.$url.'>'.$url.'</a>
                        </div>
                        </body> 
                        </html>
                    ');

        try {
            $this->mailer->send($mail);
            return new JsonResponse([
                'status' => 'success',
                'code' => 200,
                'message' => "Mail envoyé"
            ]);
        }catch (\Exception $e){
            return new JsonResponse([
                'status' => 'error',
                'code' => 500,
                'message' => "Un problème est survenu lors de l'envoi du mail"
            ]);
        }

    }
}