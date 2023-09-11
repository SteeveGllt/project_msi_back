<?php

namespace App\DataFixtures;

use App\Entity\Commentaire;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ZcommentaireFixtures extends Fixture
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $ticket = $this->em->getRepository(Ticket::class)->findOneBy(['mail_expediteur' => 'mdefroissard@tkorp.com']);
        $user = $this->em->getRepository(User::class)->findOneBy(['prenom'=>'Emilien']);
        $ticket->addUtilisateur($user);
        $commentaire = new Commentaire();
        $commentaire->setContenu('elle pt les couilles celle la');
        $commentaire->setCreated(new \DateTime('now'));
        $commentaire->setTicket($ticket);
        $commentaire->setUtilisateur($user);
        $manager->persist($commentaire);
        $manager->flush();
    }
}
