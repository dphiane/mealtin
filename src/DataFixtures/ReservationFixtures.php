<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Reservation;
use DateTime;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ReservationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create();
        for($i=0; $i < 20;$i++){
            $reservation = new Reservation();
            $reservation->setDate(new DateTimeImmutable('now'+'1d'));
        }
        $manager->flush();
    }
}
