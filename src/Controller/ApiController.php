<?php

namespace App\Controller;

use App\Repository\DisponibilityRepository;
use App\Repository\ReservationRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function disponibility(Request $request,DisponibilityRepository $disponibilityRepository, ReservationRepository $reservationRepository): JsonResponse
    {   
        $date= $request->query->get('date');
        $dateTimeImmutable= new DateTimeImmutable($date);
        $reservation = $reservationRepository->findOneBy(['date'=> $dateTimeImmutable]);

        if(!$reservation){
            return new JsonResponse([]);
        }

        $dispo_id = $reservation->getDisponibility();
        $dispo = $disponibilityRepository->findOneBy(['id'=>$dispo_id]);

        $dispoToJson=['maxReservationLunch' =>$dispo->getMaxReservationLunch(),
                    'maxReservationDiner'=> $dispo->getMaxReservationDiner(),
                    'maxSeatLunch'=>$dispo->getMaxSeatLunch(),
                    'maxSeatDiner'=>$dispo->getMaxSeatDiner(),
                    'hour'=>$reservation->getTime()->format("H"),
                    'minute'=>$reservation->getTime()->format("i"),
                    'howManyGuest'=>$reservation->getHowManyGuest(),
                    'date'=>$reservation->getDate()->format("Y-m-d"),
        ];

        return new JsonResponse($dispoToJson);
    }
}
