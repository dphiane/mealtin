<?php

namespace App\DataFixtures;

use App\Entity\Disponibility;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use App\Service\ReservationService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

// La création de fixture peuvent générer bug car vous aller tomber sur des disponibilité n'ayant pas assez de place pour la réservation
// php bin/console doctrine:fixtures:load --append
class ReservationFixtures extends Fixture
{
    public function __construct(private ReservationService $reservationService, private ReservationRepository $reservationRepository, private UserRepository $userRepository)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 100; ++$i) {
            $date = \DateTimeImmutable::createFromMutable($faker->dateTimeBetween('now', '+2 month'));
            $reservations = $this->reservationRepository->findOneBy(['date' => $date]);
            $users = $this->userRepository->findAll();

            $reservation = new Reservation();
            $randomUser = $users[array_rand($users)];
            $reservation->setUser($randomUser);
            $reservation->setDate($date);
            $hour = $faker->randomElement([12, 13, 19, 20]);
            $minute = $faker->randomElement([0, 15, 30, 45]);
            $time = \DateTimeImmutable::createFromFormat('H:i', sprintf('%02d:%02d', $hour, $minute));
            $reservation->setTime($time);
            $reservation->setHowManyGuest($faker->numberBetween(1, 10));
            if ($reservations) {
                $disponibility = $this->reservationService->checkDisponibilityAndUpdateEntity($reservations->getDisponibility(), $reservation);
            } else {
                // Si aucune réservation n'est trouvée, créez une nouvelle disponibilité
                $disponibility = new Disponibility();
                $reservationTime = $reservation->getTime()->format('H:i');
                $howManyGuest = $reservation->getHowManyGuest();

                if ($reservationTime >= '12:00' && $reservationTime <= '14:00') {
                    $this->reservationService->createNewReservationLunch($disponibility, $howManyGuest);
                } elseif ($reservationTime >= '19:00' && $reservationTime <= '21:00') {
                    $this->reservationService->createNewReservationDiner($disponibility, $howManyGuest);
                }
                $manager->persist($disponibility);
            }
            $manager->persist($reservation);
            $reservation->setDisponibility($disponibility);
            $manager->flush();
        }
    }
}
