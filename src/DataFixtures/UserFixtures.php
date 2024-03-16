<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $user =new User();
        $user->setEmail("Azerty@azerty.com")
        ->setFirstname("azerty")
        ->setLastname("Qwerty")
        ->setTelephone("0760423143")
        ->setPassword("azerty");
        $manager->persist($user);
        $manager->flush();
    }
}
