<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 1; ++$i) {
            $user = new User();
            // $password = $this->hasher->hashPassword($user,$faker->password(6));
            $user->setEmail($faker->email())
            ->setFirstname($faker->firstName())
            ->setLastname($faker->lastName())
            ->setTelephone($faker->phoneNumber())
            ->setPassword($faker->password(6));
            $manager->persist($user);
        }
        $manager->flush();
    }
}
