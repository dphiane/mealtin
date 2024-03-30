<?php

namespace App\Controller;
use App\Repository\DisponibilityRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function disponibility(Request $request, DisponibilityRepository $disponibilityRepository, ReservationRepository $reservationRepository): JsonResponse
    {
        $date = $request->query->get('date');
        $dateTimeImmutable = new \DateTimeImmutable($date);
        $reservation = $reservationRepository->findOneBy(['date' => $dateTimeImmutable]);

        if (!$reservation) {
            return new JsonResponse([]);
        }

        $dispo_id = $reservation->getDisponibility();
        $dispo = $disponibilityRepository->findOneBy(['id' => $dispo_id]);

        $dispoToJson = [];

        if (null !== $dispo) {
            $dispoToJson['maxReservationLunch'] = $dispo->getMaxReservationLunch();
            $dispoToJson['maxReservationDiner'] = $dispo->getMaxReservationDiner();
            $dispoToJson['maxSeatLunch'] = $dispo->getMaxSeatLunch();
            $dispoToJson['maxSeatDiner'] = $dispo->getMaxSeatDiner();
        }

        if (null !== $reservation) {
            $dispoToJson['hour'] = $reservation->getTime()->format('H');
            $dispoToJson['minute'] = $reservation->getTime()->format('i');
            $dispoToJson['howManyGuest'] = $reservation->getHowManyGuest();
            $dispoToJson['date'] = $reservation->getDate()->format('Y-m-d');
        }

        return new JsonResponse($dispoToJson);
    }
}
