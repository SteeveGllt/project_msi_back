<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use App\Entity\Salle;
use App\Entity\Ticket;
use App\Entity\TicketLogs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class TicketFixtures extends Fixture
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $etat = $this->em->getRepository(Etat::class)->findOneBy(['libelle'=>'vu']);

        $salle = new Salle();
        $salle->setLibelle("Défaut");
        $manager->persist($salle);
        $manager->flush();
        $ticket = new Ticket();
        $ticket->setMailExpediteur('mdefroissard@tkorp.com');
        $ticket->setMailDestinataire(array('info@tkrop.com'));
        $ticket->setObjet('pc kc');
        $ticket->setDescription('le pc de la star est kc');
        $ticket->setDateCreation(new \DateTime('2022-12-23'));
        $ticket->setDateLimite(new \DateTime('2024-12-13'));
        $ticket->setRepondu(true);
        $ticket->setEtat($etat);
        $ticket->setSalle($salle);
        $manager->persist($ticket);

        $ticketLogs = new TicketLogs();
        $ticketLogs->setIsLastNew(false);
        $manager->persist($ticketLogs);
        $manager->flush();
    }
}
