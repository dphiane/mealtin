<?php

namespace App\Controller;
use App\Form\ReservationType;
use App\Repository\DisponibilityRepository;
use App\Repository\ReservationRepository;
use App\Service\EmailService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
            'reservations' => $reservations,
        ]);
    }

    #[Route('/mes-reservations/{id}', name: 'app_my_reservation_edit')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function editReservation(
        $id,
        ReservationRepository $reservationRepository,
        Request $request,
        DisponibilityRepository $disponibilityRepository,
        EntityManagerInterface $entityManagerInterface,
        EmailService $emailService,
        ReservationService $reservationService,
    ) {
        $reservation = $reservationRepository->findOneBy(['id' => $id]);

        $this->denyAccessUnlessGranted('ACCESS_EDIT_RESERVATION', $reservation);

        $originalReservation = clone $reservation;
        $disponibility = $disponibilityRepository->findOneBy(['id' => $reservation->getDisponibility()]);
        $reservationForm = $this->createForm(ReservationType::class, $reservation);

        $dateReservation = $reservation->getDate()->format('Y-m-d');
        $reservationTime = $reservation->getTime();

        $reservationForm->handleRequest($request);
        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            try {
                $reservations = $reservationRepository->findOneBy(['date' => $reservation->getDate()]);

                $reservationService->handleEditReservation($disponibility, $originalReservation, $reservations, $reservation);
                $entityManagerInterface->persist($disponibility);
                $entityManagerInterface->persist($reservation);
                $entityManagerInterface->flush();

                $this->addFlash('success', 'Votre réservation a bien été modifiée');
                $emailService->sendConfirmModifyEmail($reservation->getUser()->getEmail(), $reservation->getDate()->format("d-m-Y"), $reservation->getTime()->format('H:i'), $reservation->getHowManyGuest());

                return $this->redirectToRoute('app_my_reservation', ['modifiée' => 1]);
            } catch (\Exception $e) {
                // Afficher les erreurs
                $this->addFlash('warning', $e->getMessage());

                return new Response('Bad Request', 400);
            }
        }

        return $this->render('my_reservation/edit.html.twig', [
            'reservationForm' => $reservationForm->createView(),
            'dateReservation' => $dateReservation,
            'reservationTime' => $reservationTime,
        ]);
    }

    #[Route('/annuler-reservation/{id}', name: 'app_my_reservation_cancel')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function cancelReservation($id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManagerInterface)
    {
        // Récupérer l'entité de la réservation à supprimer
        $reservation = $reservationRepository->find($id);
        $this->denyAccessUnlessGranted('ACCESS_EDIT_RESERVATION', $reservation);

        // Vérifier si la réservation existe
        if (!$reservation) {
            throw $this->createNotFoundException('La réservation n\'existe pas');
        }

        // Supprimer l'entité de la base de données
        $entityManagerInterface->remove($reservation);
        $entityManagerInterface->flush();

        // Répondre avec une réponse de succès
        $this->addFlash('success', 'Votre réservation a bien été annulée');

        return $this->redirectToRoute('app_my_reservation');
    }
}
