<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void{
        $etat = new Etat();
        $etat->setLibelle('vu');
        $etat->setOrdre(1);
        $etat->setColor("3a25ff");
        $manager->persist($etat);

        $etat2 = new Etat();
        $etat2->setLibelle('en attente');
        $etat2->setOrdre(2);
        $etat2->setColor("12ffba");
        $manager->persist($etat2);

        $etat2 = new Etat();
        $etat2->setLibelle('Terminé');
        $etat2->setOrdre(3);
        $etat2->setColor("bcdf77");
        $manager->persist($etat2);


        $manager->flush();
    }
}
