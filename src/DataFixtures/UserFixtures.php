<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder) {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setNom('Paul');
        $user->setPrenom('Emilien');
        $user->setEmail('epaul@groupemontroland.fr');
        $encoded = $this->encoder->hashPassword($user, 'azerty');
        $user->setPassword($encoded);
        $user->setRoles(array('ROLE_ADMIN'));
        $user->setColor("FFBA66");
        $manager->persist($user);

        $user2 = new User();
        $user2->setNom('Jeannerod');
        $user2->setPrenom('Benoit');
        $user2->setEmail('bjeannerod@groupemontroland.fr');
        $encoded = $this->encoder->hashPassword($user2,'azerty');
        $user2->setPassword($encoded);
        $user2->setRoles(array('ROLE_ADMIN'));
        $user2->setColor("B7A2D4");
        $manager->persist($user2);

        $user2 = new User();
        $user2->setNom('Bailly');
        $user2->setPrenom('Olivier');
        $user2->setEmail('obailly@groupemontroland.fr');
        $encoded = $this->encoder->hashPassword($user2,'azerty');
        $user2->setPassword($encoded);
        $user2->setRoles(array('ROLE_ADMIN'));
        $user2->setColor("BBB5AD");
        $manager->persist($user2);

        $user2 = new User();
        $user2->setNom('Guillot');
        $user2->setPrenom('Steeve');
        $user2->setEmail('sguillot@groupemontroland.fr');
        $encoded = $this->encoder->hashPassword($user2,'azerty');
        $user2->setPassword($encoded);
        $user2->setRoles(array('ROLE_ADMIN'));
        $user2->setColor("E8DA3C");
        $manager->persist($user2);

        $user2 = new User();
        $user2->setNom('Le Touzic');
        $user2->setPrenom('Ethan');
        $user2->setEmail('eletouzic@groupemontroland.fr');
        $encoded = $this->encoder->hashPassword($user2,'azerty');
        $user2->setPassword($encoded);
        $user2->setRoles(array('ROLE_ADMIN'));
        $user2->setColor("3CE844");
        $manager->persist($user2);

        $manager->flush();
    }
}
