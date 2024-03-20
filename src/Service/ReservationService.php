<?php

namespace App\Service;

use Exception;
use DateTimeImmutable;
use App\Entity\Reservation;
use App\Entity\Disponibility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationService extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function handleEditReservation(Disponibility $disponibility, Reservation $originalReservation, $reservations, $modifyReservation)
    {
        $oldDate = $originalReservation->getDate();
        $reservationTime = $originalReservation->getTime()->format("H:i");
        $reservationHowManyGuest = $originalReservation->getHowManyGuest();
        $disponibilityMaxSeatDiner = $disponibility->getMaxSeatDiner();
        $disponibilityMaxSeatLunch = $disponibility->getMaxSeatLunch();
        $disponibilityMaxReservationLunch = $disponibility->getMaxReservationLunch();
        $disponibilityMaxReservationDiner = $disponibility->getMaxReservationDiner();
        $newReservationTimeFormated = $modifyReservation->getTime()->format('H:i');
        $newReservationHowManyGuest = $modifyReservation->getHowManyGuest();
        $newDate = $modifyReservation->getDate();
        $today = new DateTimeImmutable("now");
        $timeOfToday = $today->format('H:i');

        $this->validateTimeAndDate($newReservationTimeFormated, $newDate, $today, $timeOfToday);

        if ($oldDate->format('Y-m-d') ===  $newDate->format('Y-m-d')) {
            $this->handleEditReservationSameDay(
                $reservationTime,
                $newReservationTimeFormated,
                $disponibilityMaxSeatDiner,
                $newReservationHowManyGuest,
                $originalReservation,
                $disponibility,
                $disponibilityMaxReservationLunch,
                $disponibilityMaxReservationDiner,
                $disponibilityMaxSeatLunch,
                $reservationHowManyGuest
            );
            //si déplacement de jour de réservation
        } else {
            $this->handleEditReservationDifferentDay(
                $reservations,
                $originalReservation,
                $reservationTime,
                $reservationHowManyGuest,
                $disponibility,
                $modifyReservation,
                $newReservationTimeFormated,
                $newReservationHowManyGuest
            );
        }
    }

    public function handleEditReservationDifferentDay($reservations, $originalReservation, $reservationTime, $reservationHowManyGuest, $disponibility, $modifyReservation, $newReservationTimeFormated, $newReservationHowManyGuest)
    {
        // si une réservation à déja créer une entity Disponibility, on la récupère pour modifier les dispo
        if ($reservations) {
            $oldDisponibility = $originalReservation->getDisponibility();
            $newDisponibility = $reservations->getDisponibility();

            if ($reservationTime >= '12:00' && $reservationTime <= '14:00') {
                $this->resetOldDisponibilityLunch($oldDisponibility, $reservationHowManyGuest);
            } elseif ($reservationTime >= '19:00' && $reservationTime <= '21:00') {
                $this->resetOldDisponibilityDiner($oldDisponibility, $reservationHowManyGuest);
            } else {
                throw new Exception("Veuillez entrer un horaire valide !");
            }

            // Vérification des plages horaires de réservation
            if ($reservationTime >= "12:00" && $reservationTime <= "14:00") {
                $disponibilityReservation = $disponibility->getMaxReservationLunch();
                $disponibilityMaxSeat = $disponibility->getMaxSeatLunch();
            } elseif ($reservationTime >= "19:00" && $reservationTime <= "21:00") {
                $disponibilityReservation = $disponibility->getMaxReservationDiner();
                $disponibilityMaxSeat = $disponibility->getMaxSeatDiner();
            } else {
                // Gestion des horaires invalides
                throw new Exception('Veuillez entrer des horaires valides !');
            }

            // Vérification de la disponibilité des places
            if ($disponibilityMaxSeat - $newReservationHowManyGuest < 0 || $disponibilityReservation - 1 < 0) {
                throw new Exception('Malheureusement, nous n\'avons pas assez de places.');
            }
            // mise a jour de la disponibilité en fonction des horaires indiqué
            if ($newReservationTimeFormated >= '12:00' && $newReservationTimeFormated <= '14:00') {
                $this->updateLunchDisponibilityWhenDateChange($newDisponibility, $newReservationHowManyGuest);
            } elseif ($newReservationTimeFormated >= '19:00' && $newReservationTimeFormated <= '21:00') {
                $this->updateDinerDisponibilityWhenDateChange($newDisponibility, $newReservationHowManyGuest);
            } else {
                throw new Exception("Une erreur est survenue lors de votre modification");
            }
            $modifyReservation->setDisponibility($newDisponibility);

            // sinon on reset l'ancienne disponibility et créer un nouvelle instance 
        } else {
            $newDisponibility = new Disponibility();

            if ($reservationTime >= '12:00' && $reservationTime <= '14:00') {
                $this->resetOldDisponibilityLunch($disponibility, $reservationHowManyGuest);
            } elseif ($reservationTime >= '19:00' && $reservationTime <= '21:00') {
                $this->resetOldDisponibilityDiner($disponibility, $reservationHowManyGuest);
            } else {
                throw new Exception("Veuillez entrer un horaire valide !");
            }

            if ($newReservationTimeFormated >= '12:00' && $newReservationTimeFormated <= '14:00') {
                $this->newReservationLunch($newDisponibility, $newReservationHowManyGuest,);
            } elseif ($newReservationTimeFormated >= '19:00' && $newReservationTimeFormated <= '21:00') {
                $this->newReservationDiner($newDisponibility, $newReservationHowManyGuest);
            } else {
                throw new Exception("Une erreur est survenue lors de votre modification");
            }
            $this->entityManager->persist($newDisponibility);
            $this->entityManager->flush();
            $modifyReservation->setDisponibility($newDisponibility);
        }
    }
    
    public function handleEditReservationSameDay($reservationTime, $newReservationTimeFormated, $disponibilityMaxSeatDiner, $newReservationHowManyGuest, $originalReservation, $disponibility, $disponibilityMaxReservationLunch, $disponibilityMaxReservationDiner, $disponibilityMaxSeatLunch, $reservationHowManyGuest)
    {
        //réservation du midi vers le soir
        if ($reservationTime >= "12:00" && $reservationTime <= "14:00" && $newReservationTimeFormated >= "19:00" && $newReservationTimeFormated <= "21:00") {

            if ($disponibilityMaxSeatDiner - $newReservationHowManyGuest >= 0) {
                $this->setLunchToDiner($originalReservation, $newReservationHowManyGuest, $disponibility, $disponibilityMaxReservationLunch, $disponibilityMaxReservationDiner, $disponibilityMaxSeatLunch, $disponibilityMaxSeatDiner, $reservationHowManyGuest);
            } else {
                throw new \Exception("Malheuresement nous n'avons plus assez de place pour le diner");
            }
            //réservation du soir vers le midi
        } elseif ($reservationTime >= "19:00" && $reservationTime <= "21:00" && $newReservationTimeFormated >= "12:00" && $newReservationTimeFormated <= "14:00") {

            if ($disponibilityMaxSeatLunch - $newReservationHowManyGuest >= 0) {
                $this->setDinerToLunch($originalReservation, $disponibility, $newReservationHowManyGuest, $disponibilityMaxReservationDiner, $disponibilityMaxReservationLunch, $disponibilityMaxSeatDiner, $disponibilityMaxSeatLunch, $reservationHowManyGuest);
            } else {
                throw new \Exception("Malheuresement nous n'avons plus assez de place pour le midi");
            }
        } else {
            //réservation du midi non changé
            if ($newReservationTimeFormated >= "12:00" && $newReservationTimeFormated <= "14:00") {

                if ($disponibilityMaxSeatLunch - ($newReservationHowManyGuest - $reservationHowManyGuest) >= 0) {
                    $this->setLunch($disponibility, $disponibilityMaxSeatLunch, $newReservationHowManyGuest, $reservationHowManyGuest, $originalReservation);
                } else {
                    throw new \Exception("Malheuresement nous n'avons plus assez de place");
                }
                //réservation du soir non changé
            } elseif ($newReservationTimeFormated >= "19:00" && $newReservationTimeFormated <= "21:00") {

                if ($disponibilityMaxSeatDiner - ($newReservationHowManyGuest - $reservationHowManyGuest) >= 0) {
                    $this->setDiner($disponibility, $disponibilityMaxSeatDiner, $newReservationHowManyGuest, $reservationHowManyGuest, $originalReservation);
                } else {
                    throw new \Exception("Malheuresement nous n'avons plus assez de place");
                }
                // le petit malin a contourner le front!  
            } else {
                throw new \Exception("Veuillez respecter les crénaux horaires!");
            }
        }
    }
    public function updateDinerDisponibilityWhenDateChange($newDisponibility, $howManyGuest)
    {
        $newDisponibility
            ->setMaxReservationDiner($newDisponibility->getMaxReservationDiner() - 1)
            ->setMaxSeatDiner($newDisponibility->getMaxSeatDiner() - $howManyGuest);
    }
    public function updateLunchDisponibilityWhenDateChange($newDisponibility, $howManyGuest)
    {
        $newDisponibility
            ->setMaxReservationLunch($newDisponibility->getMaxReservationLunch() - 1)
            ->setMaxSeatLunch($newDisponibility->getMaxSeatLunch() - $howManyGuest);
    }
    //créer une nouvelle disponibility pour le midi
    public function newReservationLunch($disponibility, $howManyGuest)
    {
        $disponibility
            ->setMaxReservationLunch(12)
            ->setMaxSeatLunch(40 - $howManyGuest)
            ->setMaxReservationDiner(13)
            ->setMaxSeatDiner(40);
    }

    //créer une nouvelle disponibility pour le soir
    public function newReservationDiner($disponibility, $howManyGuest)
    {
        $disponibility
            ->setMaxReservationDiner(12)
            ->setMaxSeatDiner(40 - $howManyGuest)
            ->setMaxReservationLunch(13)
            ->setMaxSeatLunch(40);
    }

    // reset la disponility du diner afin de supprimer la réservation annulé ou changé de date 
    public function resetOldDisponibilityDiner($disponibility, $reservationHowManyGuest)
    {
        $disponibility->setMaxReservationDiner($disponibility->getMaxReservationDiner() + 1)
            ->setMaxSeatDiner($disponibility->getMaxSeatDiner() + $reservationHowManyGuest);
    }

    // reset la disponility du lunch afin de supprimer la réservation annulé ou changé de date 
    public function resetOldDisponibilityLunch($disponibility, $reservationHowManyGuest)
    {
        $disponibility
            ->setMaxReservationLunch($disponibility->getMaxReservationLunch() + 1)
            ->setMaxSeatLunch($disponibility->getMaxSeatLunch() + $reservationHowManyGuest);
    }

    // déplacer la résa et dispo du midi vers le soir
    public function setLunchToDiner(
        $reservation,
        $newReservationHowManyGuest,
        $disponibility,
        $disponibilityMaxReservationLunch,
        $disponibilityMaxReservationDiner,
        $disponibilityMaxSeatLunch,
        $disponibilityMaxSeatDiner,
        $reservationHowManyGuest
    ) {
        $reservation->setHowManyGuest($newReservationHowManyGuest);
        $disponibility
            ->setMaxReservationLunch($disponibilityMaxReservationLunch + 1)
            ->setMaxReservationDiner($disponibilityMaxReservationDiner - 1)
            ->setMaxSeatLunch($disponibilityMaxSeatLunch + $reservationHowManyGuest)
            ->setMaxSeatDiner($disponibilityMaxSeatDiner - $newReservationHowManyGuest);
    }

    // déplacer la résa et dispo du soir vers le midi
    public function setDinerToLunch($reservation, $disponibility, $newReservationHowManyGuest, $disponibilityMaxReservationDiner, $disponibilityMaxReservationLunch, $disponibilityMaxSeatDiner, $disponibilityMaxSeatLunch, $reservationHowManyGuest)
    {
        $reservation->setHowManyGuest($newReservationHowManyGuest);
        $disponibility
            ->setMaxReservationDiner($disponibilityMaxReservationDiner + 1)
            ->setMaxReservationLunch($disponibilityMaxReservationLunch - 1)
            ->setMaxSeatDiner($disponibilityMaxSeatDiner + $reservationHowManyGuest)
            ->setMaxSeatLunch($disponibilityMaxSeatLunch - $newReservationHowManyGuest);
    }

    // on déduit les dispo du diner avec la nouvelle résa
    public function setDiner($disponibility, $disponibilityMaxSeatDiner, $newReservationHowManyGuest, $reservationHowManyGuest, $reservation)
    {
        $disponibility->setMaxSeatDiner($disponibilityMaxSeatDiner - ($newReservationHowManyGuest - $reservationHowManyGuest));
        $reservation->setHowManyGuest($newReservationHowManyGuest);
    }

    // on déduit les dispo du lunch avec la nouvelle résa
    public function setLunch($disponibility, $disponibilityMaxSeatLunch, $newReservationHowManyGuest, $reservationHowManyGuest, $reservation)
    {
        $disponibility->setMaxSeatLunch($disponibilityMaxSeatLunch - ($newReservationHowManyGuest - $reservationHowManyGuest));
        $reservation->setHowManyGuest($newReservationHowManyGuest);
    }

    // gestion de l'heure et date de résa antérieur ou trop lointaine
    public function validateTimeAndDate($newReservationTimeFormated, $newDate, $today, $timeOfToday)
    {
        if ($newDate->format("Y-m-d") < $today->format("Y-m-d")) {
            throw new \Exception("Votre réservation ne peut être antérieure à aujourd'hui");
        }
        if ($newReservationTimeFormated < $timeOfToday && $newDate->format("Y-m-d") == $today->format("Y-m-d")) {
            throw new \Exception("Votre réservation ne peut être antérieure à maintenant");
        }
        if ($newDate->format('Y-m-d') > $today->modify('+1 month +2 weeks')) {
            throw new \Exception("Votre réservation ne peut dépasser 1 mois et 2 semaines à partir de maintenant");
        }
    }

    // si une réservation a déjà été faite a ce jour, on récupère la disponibility et on déduit les places avec la nouvelle réservation
    public function checkDisponibilityAndUpdateEntity($disponibility, $reservation)
    {
        // Récupération de l'heure de la réservation
        $reservationTime = $reservation->getTime()->format("H:i");

        // Vérification des plages horaires de réservation
        if ($reservationTime >= "12:00" && $reservationTime <= "14:00") {
            $disponibilityReservation = $disponibility->getMaxReservationLunch();
            $disponibilityMaxSeat = $disponibility->getMaxSeatLunch();
        } elseif ($reservationTime >= "19:00" && $reservationTime <= "21:00") {
            $disponibilityReservation = $disponibility->getMaxReservationDiner();
            $disponibilityMaxSeat = $disponibility->getMaxSeatDiner();
        } else {
            // Gestion des horaires invalides
            throw new Exception('Veuillez entrer des horaires valides !');
        }

        // Vérification de la disponibilité des places
        $howManyGuest = $reservation->getHowManyGuest();
        if ($disponibilityMaxSeat - $howManyGuest < 0 || $disponibilityReservation - 1 < 0) {
            throw new Exception('Malheureusement, nous n\'avons pas assez de places.');
        }

        // Mise à jour de la nouvelle disponibilité
        if ($reservationTime <= "14:00") {
            $disponibility
                ->setMaxReservationLunch($disponibility->getMaxReservationLunch() - 1)
                ->setMaxSeatLunch($disponibility->getMaxSeatLunch() - $howManyGuest);
        } else {
            $disponibility
                ->setMaxReservationDiner($disponibility->getMaxReservationDiner() - 1)
                ->setMaxSeatDiner($disponibility->getMaxSeatDiner() - $howManyGuest);
        }
        return $disponibility;
    }
}
