<?php

namespace App\Controller;

use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\DisponibilityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyReservationController extends AbstractController
{
    #[Route('/mes-reservations', name: 'app_my_reservation')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        $reservations = $reservationRepository->findBy(['user' => $user->getId()]);

        return $this->render('my_reservation/index.html.twig', [
            'reservations' => $reservations
        ]);
    }
    #[Route('/mes-reservations/{id}', name: 'app_my_reservation_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editReservation($id, ReservationRepository $reservationRepository, Request $request, DisponibilityRepository $disponibilityRepository, EntityManagerInterface $entityManagerInterface)
    {
        $reservation = $reservationRepository->findOneBy(['id' => $id]);
        $reservationForm = $this->createForm(ReservationType::class, $reservation);
        $reservationForm->handleRequest($request);

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {

        }
        return $this->render('my_reservation/edit.html.twig', [
            'reservationForm' => $reservationForm->createView(),
        ]);
    }
    #[Route('/mes-reservations/{id}', name: 'app_my_reservation_cancel')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function cancelReservation($id)
    {
        return $this->render('my_reservation/cancel.html.twig');
    }
}
