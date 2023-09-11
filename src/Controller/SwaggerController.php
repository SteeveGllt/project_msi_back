<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


#[OA\Info(version: "1.0", title: "MSI")]
class SwaggerController extends AbstractController
{
    #[Route('/', name: 'app_swagger')]
    public function index(){
        return $this->redirect("./swagger/");
    }
}
