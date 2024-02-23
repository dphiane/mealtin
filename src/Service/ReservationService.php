<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationService extends AbstractController{
    public function ifReservation($reservations,$reservation){

        $disponibility = $reservations->getDisponibility();
        $reservationTime = $reservation->getTime()->format("H:i:s");

        $disponibilityReservationLunch = $disponibility->getMaxReservationLunch();
        $disponibilityReservationDiner = $disponibility->getMaxReservationDiner();
        $disponibilityMaxSeatDiner = $disponibility->getMaxSeatDiner();
        $disponibilityMaxSeatLunch = $disponibility->getMaxSeatLunch();

        $howManyGuest = $reservation->getHowManyGuest();
        if ($reservationTime < "14:00:00") {
            if ($disponibilityMaxSeatLunch - $howManyGuest < 0 || $disponibilityReservationLunch - 1 < 0) {
                $this->addFlash('warning', 'Malheursement nous n\'avons pas assez de place');
                return $this->redirectToRoute('app_reservation');
            }
            $disponibility
                ->setMaxReservationLunch($disponibilityReservationLunch - 1)
                ->setMaxSeatLunch($disponibilityMaxSeatLunch - $howManyGuest);
        } else {
            if ($disponibilityMaxSeatDiner - $howManyGuest < 0 || $disponibilityReservationDiner - 1 < 0) {
                $this->addFlash('warning', 'Malheursement nous n\'avons pas assez de place');
                return $this->redirectToRoute('app_reservation');
            }
            $disponibility
                ->setMaxReservationDiner($disponibilityReservationDiner - 1)
                ->setMaxSeatDiner($disponibilityMaxSeatDiner - $howManyGuest);
        }
        return $disponibility;
    }
}