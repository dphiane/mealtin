<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ReservationService extends AbstractController
{
    public function ifReservation($reservations, $reservation,Request $request)
    {
        $disponibility = $reservations->getDisponibility();
        $reservationTime = $reservation->getTime()->format("H:i");

        if ($reservationTime >= "12:00" && $reservationTime <= "14:00") {
            $disponibilityReservation = $disponibility->getMaxReservationLunch();
            $disponibilityMaxSeat = $disponibility->getMaxSeatLunch();
        } elseif($reservationTime >= "19:00" && $reservationTime <= "21:00") {
            $disponibilityReservation = $disponibility->getMaxReservationDiner();
            $disponibilityMaxSeat = $disponibility->getMaxSeatDiner();
        }else{
            $this->addFlash("warning","Veuillez entrez des horaires valides!");
            return new Response("Bad Request", 400);
        }

        $howManyGuest = $reservation->getHowManyGuest();

        if ($disponibilityMaxSeat - $howManyGuest < 0 || $disponibilityReservation - 1 < 0) {
            $this->addFlash('warning', 'Malheureusement, nous n\'avons pas assez de place');
            return new Response("Bad Request", 400);
        }

        if ($reservationTime < "14:00") {
            $disponibility
                ->setMaxReservationLunch($disponibilityReservation - 1)
                ->setMaxSeatLunch($disponibilityMaxSeat - $howManyGuest);
        } else {
            $disponibility
                ->setMaxReservationDiner($disponibilityReservation - 1)
                ->setMaxSeatDiner($disponibilityMaxSeat - $howManyGuest);
        }

        return $disponibility;
    }
}
