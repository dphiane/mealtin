<?php

namespace App\Service;

use App\Entity\Disponibility;
use App\Entity\Reservation;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationService extends AbstractController
{
    private const START_OF_SERVICE_LUNCH = "12:00";
    private const END_OF_SERVICE_LUNCH = "14:00";
    private const START_OF_SERVICE_DINER = "19:00";
    private const END_OF_SERVICE_DINER = "21:00";

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function handleEditReservation(Disponibility $disponibility, Reservation $originalReservation, ?Reservation $reservationAtThisDateToGetDispo, Reservation $modifyReservation): void
    {
        $oldDate = $originalReservation->getDate();
        $reservationTime = $originalReservation->getTime()->format('H:i');
        $reservationHowManyGuest = $originalReservation->getHowManyGuest();
        $disponibilityMaxSeatDiner = $disponibility->getMaxSeatDiner();
        $disponibilityMaxSeatLunch = $disponibility->getMaxSeatLunch();
        $disponibilityMaxReservationLunch = $disponibility->getMaxReservationLunch();
        $disponibilityMaxReservationDiner = $disponibility->getMaxReservationDiner();
        $newReservationTimeFormated = $modifyReservation->getTime()->format('H:i');
        $newReservationHowManyGuest = $modifyReservation->getHowManyGuest();
        $newDate = $modifyReservation->getDate();
        $today = new DateTimeImmutable('now');
        $timeOfToday = $today->format('H:i');

        $this->isDateAndTimeValid($newReservationTimeFormated, $newDate, $today, $timeOfToday);
        $this->isDayClose($newDate);
        
        if ($oldDate->format('Y-m-d') === $newDate->format('Y-m-d')) {
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
        } else {
            $this->handleEditReservationDifferentDay(
                $reservationAtThisDateToGetDispo,
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

    private function handleEditReservationDifferentDay(
        ?Reservation $reservationAtThisDateToGetDispo,
        Reservation $originalReservation,
        string $reservationTime,
        int $reservationHowManyGuest,
        Disponibility $disponibility,
        Reservation $modifyReservation,
        string $newReservationTimeFormated,
        int $newReservationHowManyGuest
    ): void {
        // si une réservation à déja créer une entity Disponibility, on la récupère pour modifier les dispo
        if ($reservationAtThisDateToGetDispo != null) {
            $oldDisponibility = $originalReservation->getDisponibility();
            $newDisponibility = $reservationAtThisDateToGetDispo->getDisponibility();

            //On reset l'ancienne disponibility en fonction de l'ancienne heure de réservation
            if ($this->isReservationForLunch($reservationTime)) {
                $this->resetOldDisponibilityLunch($oldDisponibility, $reservationHowManyGuest);
                $disponibilityReservation = $newDisponibility->getMaxReservationLunch();
                $disponibilityMaxSeat = $newDisponibility->getMaxSeatLunch();
            } elseif ($this->isReservationForDiner($reservationTime)) {
                $this->resetOldDisponibilityDiner($oldDisponibility, $reservationHowManyGuest);
                $disponibilityReservation = $newDisponibility->getMaxReservationDiner();
                $disponibilityMaxSeat = $newDisponibility->getMaxSeatDiner();
            } else {
                throw new Exception('Veuillez entrer un horaire valide !');
            }
            
            // on set les nouvelles disponibilitées
            if ($this->isReservationForLunch($newReservationTimeFormated)) {
                $disponibilityReservation = $newDisponibility->getMaxReservationLunch();
                $disponibilityMaxSeat = $newDisponibility->getMaxSeatLunch();
            } elseif ($this->isReservationForDiner($newReservationTimeFormated)) {
                $disponibilityReservation = $newDisponibility->getMaxReservationDiner();
                $disponibilityMaxSeat = $newDisponibility->getMaxSeatDiner();
            }

            // Vérification de la disponibilité des places
            if ($disponibilityMaxSeat - $newReservationHowManyGuest < 0 || $disponibilityReservation - 1 < 0) {
                throw new Exception('Malheureusement, nous n\'avons pas assez de places.');
            }
            // mise a jour de la disponibilité en fonction des horaires indiqué
            if ($this->isReservationForLunch($newReservationTimeFormated)) {
                $this->updateLunchDisponibilityWhenDateChange($newDisponibility, $newReservationHowManyGuest);
            } elseif ($this->isReservationForDiner($newReservationTimeFormated)) {
                $this->updateDinerDisponibilityWhenDateChange($newDisponibility, $newReservationHowManyGuest);
            } else {
                throw new Exception('Une erreur est survenue lors de votre modification');
            }
            $modifyReservation->setDisponibility($newDisponibility);

            // sinon on reset l'ancienne disponibility et créer un nouvelle instance
        } else {
            $newDisponibility = new Disponibility();

            if ($this->isReservationForLunch($reservationTime)) {
                $this->resetOldDisponibilityLunch($disponibility, $reservationHowManyGuest);
            } elseif ($this->isReservationForDiner($reservationTime)) {
                $this->resetOldDisponibilityDiner($disponibility, $reservationHowManyGuest);
            } else {
                throw new Exception('Veuillez entrer un horaire valide !');
            }

            if ($this->isReservationForLunch($newReservationTimeFormated)) {
                $this->createNewReservationLunch($newDisponibility, $newReservationHowManyGuest);
            } elseif ($this->isReservationForDiner($newReservationTimeFormated)) {
                $this->createNewReservationDiner($newDisponibility, $newReservationHowManyGuest);
            } else {
                throw new Exception('Une erreur est survenue lors de votre modification');
            }

            $this->entityManager->persist($newDisponibility);
            $this->entityManager->flush();
            $modifyReservation->setDisponibility($newDisponibility);
        }
    }
    
    private function updateDinerDisponibilityWhenDateChange(Disponibility $newDisponibility, int $howManyGuest): void
    {
        $newDisponibility
            ->setMaxReservationDiner($newDisponibility->getMaxReservationDiner() - 1)
            ->setMaxSeatDiner($newDisponibility->getMaxSeatDiner() - $howManyGuest);
    }

    private function updateLunchDisponibilityWhenDateChange(Disponibility $newDisponibility, int $howManyGuest): void
    {
        $newDisponibility
            ->setMaxReservationLunch($newDisponibility->getMaxReservationLunch() - 1)
            ->setMaxSeatLunch($newDisponibility->getMaxSeatLunch() - $howManyGuest);
    }

    private function handleEditReservationSameDay(
        string $reservationTime,
        string $newReservationTimeFormated,
        int $disponibilityMaxSeatDiner,
        int $newReservationHowManyGuest,
        Reservation $originalReservation,
        Disponibility $disponibility,
        int $disponibilityMaxReservationLunch,
        int $disponibilityMaxReservationDiner,
        int $disponibilityMaxSeatLunch,
        int $reservationHowManyGuest
    ): void {
        // réservation du midi vers le soir
        if ($this->isMovingLunchToDiner($reservationTime, $newReservationTimeFormated)) {
            if ($disponibilityMaxSeatDiner - $newReservationHowManyGuest >= 0) {
                $this->moveLunchToDiner($originalReservation, $newReservationHowManyGuest, $disponibility, $disponibilityMaxReservationLunch, $disponibilityMaxReservationDiner, $disponibilityMaxSeatLunch, $disponibilityMaxSeatDiner, $reservationHowManyGuest);
            } else {
                throw new Exception("Malheuresement nous n'avons plus assez de place pour le diner");
            }
            // réservation du soir vers le midi
        } elseif ($this->isMovingDinerToLunch($reservationTime, $newReservationTimeFormated)) {
            if ($disponibilityMaxSeatLunch - $newReservationHowManyGuest >= 0) {
                $this->moveDinerToLunch($originalReservation, $disponibility, $newReservationHowManyGuest, $disponibilityMaxReservationDiner, $disponibilityMaxReservationLunch, $disponibilityMaxSeatDiner, $disponibilityMaxSeatLunch, $reservationHowManyGuest);
            } else {
                throw new Exception("Malheuresement nous n'avons plus assez de place pour le midi");
            }
        } else {
            // réservation du midi non changé
            if ($this->isReservationForLunch($newReservationTimeFormated)) {
                if ($disponibilityMaxSeatLunch - ($newReservationHowManyGuest - $reservationHowManyGuest) >= 0) {
                    $this->updateSeatLunchAndHowManyGuest($disponibility, $disponibilityMaxSeatLunch, $newReservationHowManyGuest, $reservationHowManyGuest, $originalReservation);
                } else {
                    throw new Exception("Malheuresement nous n'avons plus assez de place");
                }
                // réservation du soir non changé
            } elseif ($this->isReservationForDiner($newReservationTimeFormated)) {
                if ($disponibilityMaxSeatDiner - ($newReservationHowManyGuest - $reservationHowManyGuest) >= 0) {
                    $this->updateSeatDinerAndHowManyGuest($disponibility, $disponibilityMaxSeatDiner, $newReservationHowManyGuest, $reservationHowManyGuest, $originalReservation);
                } else {
                    throw new Exception("Malheuresement nous n'avons plus assez de place");
                }
            } else {
                throw new Exception('Veuillez respecter les crénaux horaires!');
            }
        }
    }

    // reset la disponility du diner afin de supprimer la réservation annulé ou changé de date
    private function resetOldDisponibilityDiner(Disponibility $disponibility, int $reservationHowManyGuest): void
    {
        $disponibility
            ->setMaxReservationDiner($disponibility->getMaxReservationDiner() + 1)
            ->setMaxSeatDiner($disponibility->getMaxSeatDiner() + $reservationHowManyGuest);
    }

    // reset la disponility du lunch afin de supprimer la réservation annulé ou changé de date
    private function resetOldDisponibilityLunch(Disponibility $disponibility, int $reservationHowManyGuest): void
    {
        $disponibility
            ->setMaxReservationLunch($disponibility->getMaxReservationLunch() + 1)
            ->setMaxSeatLunch($disponibility->getMaxSeatLunch() + $reservationHowManyGuest);
    }

    // déplacer la résa et dispo du midi vers le soir
    private function moveLunchToDiner(
        Reservation $reservation,
        int $newReservationHowManyGuest,
        Disponibility $disponibility,
        int $disponibilityMaxReservationLunch,
        int $disponibilityMaxReservationDiner,
        int $disponibilityMaxSeatLunch,
        int $disponibilityMaxSeatDiner,
        int $reservationHowManyGuest
    ): void {
        $reservation->setHowManyGuest($newReservationHowManyGuest);
        $disponibility
            ->setMaxReservationLunch($disponibilityMaxReservationLunch + 1)
            ->setMaxReservationDiner($disponibilityMaxReservationDiner - 1)
            ->setMaxSeatLunch($disponibilityMaxSeatLunch + $reservationHowManyGuest)
            ->setMaxSeatDiner($disponibilityMaxSeatDiner - $newReservationHowManyGuest);
    }

    private function isMovingLunchToDiner(string $reservationTime, string $newReservationTime): bool
    {
        return ($reservationTime >= SELF::START_OF_SERVICE_LUNCH && $reservationTime <= SELF::END_OF_SERVICE_LUNCH)
            && ($newReservationTime >= SELF::START_OF_SERVICE_DINER && $newReservationTime <= SELF::END_OF_SERVICE_DINER);
    }

    // déplacer la résa et dispo du soir vers le midi
    private function moveDinerToLunch(
        Reservation $reservation,
        Disponibility $disponibility,
        int $newReservationHowManyGuest,
        int $disponibilityMaxReservationDiner,
        int $disponibilityMaxReservationLunch,
        int $disponibilityMaxSeatDiner,
        int $disponibilityMaxSeatLunch,
        int $reservationHowManyGuest
    ): void {
        $reservation->setHowManyGuest($newReservationHowManyGuest);
        $disponibility
            ->setMaxReservationDiner($disponibilityMaxReservationDiner + 1)
            ->setMaxReservationLunch($disponibilityMaxReservationLunch - 1)
            ->setMaxSeatDiner($disponibilityMaxSeatDiner + $reservationHowManyGuest)
            ->setMaxSeatLunch($disponibilityMaxSeatLunch - $newReservationHowManyGuest);
    }

    private function isMovingDinerToLunch(string $reservationTime, string $newReservationTime): bool
    {
        return ($reservationTime >= SELF::START_OF_SERVICE_DINER && $reservationTime <= SELF::END_OF_SERVICE_DINER)
            && ($newReservationTime >= SELF::START_OF_SERVICE_LUNCH && $newReservationTime <= SELF::END_OF_SERVICE_LUNCH);
    }

    // cette fonction permet de mettre à jour la disponibility et reservation lors d'un changement de nombre de personne
    private function updateSeatDinerAndHowManyGuest(
        Disponibility $disponibility,
        int $disponibilityMaxSeatDiner,
        int $newReservationHowManyGuest,
        int $reservationHowManyGuest,
        Reservation $reservation
    ): void {
        $disponibility->setMaxSeatDiner($disponibilityMaxSeatDiner - ($newReservationHowManyGuest - $reservationHowManyGuest));
        $reservation->setHowManyGuest($newReservationHowManyGuest);
    }
    
    // cette fonction permet de mettre à jour la disponibility et reservation lors d'un changement de nombre de personne
    private function updateSeatLunchAndHowManyGuest(
        Disponibility $disponibility,
        int $disponibilityMaxSeatLunch,
        int $newReservationHowManyGuest,
        int $reservationHowManyGuest,
        Reservation $reservation
    ): void {
        $disponibility->setMaxSeatLunch($disponibilityMaxSeatLunch - ($newReservationHowManyGuest - $reservationHowManyGuest));
        $reservation->setHowManyGuest($newReservationHowManyGuest);
    }

    public function handleNewReservation(Disponibility $disponibility, Reservation $reservation): Disponibility
    {
        $reservationTime = $reservation->getTime()->format('H:i');
        $howManyGuest = $reservation->getHowManyGuest();

        $this->isDayClose($reservation->getDate());

        if ($this->isReservationForLunch($reservationTime)) {
            $this->createNewReservationLunch($disponibility, $howManyGuest);
        } elseif ($this->isReservationForDiner($reservationTime)) {
            $this->createNewReservationDiner($disponibility, $howManyGuest);
        } else {
            throw new Exception('Veuillez entrer un horaire valide !');
        }
        return $disponibility;
    }
    // créer une nouvelle disponibility pour le midi
    public function createNewReservationLunch(Disponibility $disponibility, int $howManyGuest): void
    {
        $disponibility
            ->setMaxReservationLunch(12)
            ->setMaxSeatLunch(40 - $howManyGuest)
            ->setMaxReservationDiner(13)
            ->setMaxSeatDiner(40);
    }

    // créer une nouvelle disponibility pour le soir
    public function createNewReservationDiner(Disponibility $disponibility, int $howManyGuest): void
    {
        $disponibility
            ->setMaxReservationDiner(12)
            ->setMaxSeatDiner(40 - $howManyGuest)
            ->setMaxReservationLunch(13)
            ->setMaxSeatLunch(40);
    }

    // gestion de l'heure et date de résa antérieur ou trop lointaine
    public function isDateAndTimeValid(
        string $timeOfReservation,
        DateTimeImmutable $dateOfReservation,
        DateTimeImmutable $dateOfToday,
        string $timeOfToday
    ): bool {
        if ($dateOfReservation->format('Y-m-d') < $dateOfToday->format('Y-m-d')) {
            throw new Exception("Votre réservation ne peut être antérieure à aujourd'hui");
        }
        if ($timeOfReservation < $timeOfToday && $dateOfReservation->format('Y-m-d') == $dateOfToday->format('Y-m-d')) {
            throw new Exception('Votre réservation ne peut être antérieure à maintenant');
        }
        if ($dateOfReservation->format('Y-m-d') > $dateOfToday->modify('+1 month +2 weeks')) {
            throw new Exception('Votre réservation ne peut dépasser 1 mois et 2 semaines à partir de maintenant');
        }
        return true;
    }
    
    private function isDayClose(DateTimeImmutable $dateOfReservation){
        if ($dateOfReservation->format('N') == 7 || $dateOfReservation->format('N') == 1) {
            throw new Exception('Les réservations ne sont pas acceptées les dimanches et lundis.');
        }
        return true;
    }

    // si une réservation a déjà été faite a ce jour, on récupère la disponibility et on déduit les places avec la nouvelle réservation
    public function checkDisponibilityAndUpdateEntity(Disponibility $disponibility, Reservation $reservation): Disponibility
    {
        // Récupération de l'heure de la réservation
        $reservationTime = $reservation->getTime()->format('H:i');

        // Vérification des plages horaires de réservation
        if ($this->isReservationForLunch($reservationTime)) {
            $numberOfReservationAvailable = $disponibility->getMaxReservationLunch();
            $numberOfSeatAvailable = $disponibility->getMaxSeatLunch();
        } elseif ($this->isReservationForDiner($reservationTime)) {
            $numberOfReservationAvailable = $disponibility->getMaxReservationDiner();
            $numberOfSeatAvailable = $disponibility->getMaxSeatDiner();
        } else {
            // Gestion des horaires invalides
            throw new Exception('Veuillez entrer des horaires valides !');
        }

        // Vérification de la disponibilité des places
        $howManyGuest = $reservation->getHowManyGuest();
        if (!$this->verifyDisponibilityAvailable($numberOfSeatAvailable, $numberOfReservationAvailable, $howManyGuest)) {
            throw new Exception('Malheureusement, nous n\'avons pas assez de places.');
        }

        // Mise à jour de la nouvelle disponibilité
        if ($this->isReservationForLunch($reservationTime)) {
            $disponibility
                ->setMaxReservationLunch($disponibility->getMaxReservationLunch() - 1)
                ->setMaxSeatLunch($disponibility->getMaxSeatLunch() - $howManyGuest);
        } elseif ($this->isReservationForDiner($reservationTime)) {
            $disponibility
                ->setMaxReservationDiner($disponibility->getMaxReservationDiner() - 1)
                ->setMaxSeatDiner($disponibility->getMaxSeatDiner() - $howManyGuest);
        }

        return $disponibility;
    }

    private function isReservationForLunch(string $reservationTime): bool
    {
        return $reservationTime >= SELF::START_OF_SERVICE_LUNCH && $reservationTime <= SELF::END_OF_SERVICE_LUNCH;
    }
    private function isReservationForDiner(string $reservationTime): bool
    {
        return $reservationTime >= SELF::START_OF_SERVICE_DINER && $reservationTime <= SELF::END_OF_SERVICE_DINER;
    }
    private function verifyDisponibilityAvailable(int $numberOfSeatAvailable, int $numberOfReservationAvailable, int $howManyGuest)
    {
        return $numberOfReservationAvailable - 1 >= 0 && $numberOfSeatAvailable - $howManyGuest >= 0;
    }
}
