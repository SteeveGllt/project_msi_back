<?php

namespace App\Controller;

use App\Entity\TicketLogs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class TicketLogsController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    #[Route('/api/tickets/logs', name: 'app_ticket_logs')]
    public function index(): JsonResponse
    {
        $logs = $this->em->getRepository(TicketLogs::class)->findAll();
        if($logs[0]->isIsLastNew() === true){
            return new JsonResponse([
                'nouveaux_mails' => true
            ]);
        }else{
            return new JsonResponse([
                'nouveaux_mails' => false
            ]);
        }
    }
}
