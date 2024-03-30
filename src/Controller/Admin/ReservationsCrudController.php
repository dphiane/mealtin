<?php

namespace App\Controller\Admin;

use App\Form\ReservationType;
use App\Repository\DisponibilityRepository;
use App\Repository\ReservationRepository;
use App\Service\EmailService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('admin')]
#[IsGranted('ROLE_ADMIN', message: 'You are not allowed to access the admin dashboard.')]
class ReservationsCrudController extends AbstractController
{
    #[Route('/reservations', name: 'app_admin_reservations')]
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $reservations = $reservationRepository->findBy([], ['date' => 'DESC']);
        $reservationsDay = [];
        $allReservations = true;
        // Boucler sur les réservations pour extraire les dates et les regrouper par jour
        foreach ($reservations as $reservation) {
            $dateReservation = $reservation->getDate()->format('Y-m-d');
            $reservationsDay[$dateReservation][] = $reservation;
        }

        // Paginer les résultats par mois
        $reservationsPerMonth = $paginator->paginate(
            $reservationsDay,
            $request->query->getInt('page', 1),
            15 // Définir le nombre de réservations par page
        );

        // dd($reservationsPerMonth);
        return $this->render('admin/index.html.twig', [
            'reservationsPerMonth' => $reservationsPerMonth,
            'allReservations' => $allReservations,
        ]);
    }

    #[Route('/reservation/{date}', name: 'app_admin_reservation_crud')]
    public function details(ReservationRepository $reservationRepository, string $date): Response
    {
        $date = new \DateTimeImmutable($date);
        $reservations = $reservationRepository->findBy(['date' => $date]);

        return $this->render('admin/reservations_crud/reservation_details.html.twig', [
            'reservations' => $reservations,
            'date' => $date,
        ]);
    }

    #[Route('/modifier-reservation/{id}', name: 'app_admin_reservation_edit')]
    public function editReservation(int $id, ReservationService $reservationService, ReservationRepository $reservationRepository, Request $request, DisponibilityRepository $disponibilityRepository, EntityManagerInterface $entityManagerInterface, EmailService $emailService): Response
    {
        $reservation = $reservationRepository->findOneBy(['id' => $id]);
        $originalReservation = clone $reservation;
        $disponibility = $disponibilityRepository->findOneBy(['id' => $reservation->getDisponibility()]);
        $reservationForm = $this->createForm(ReservationType::class, $reservation);

        $dateReservation = $reservation->getDate()->format('Y-m-d');

        $reservationForm->handleRequest($request);
        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {
            try {
                $reservations = $reservationRepository->findOneBy(['date' => $reservation->getDate()]);

                $reservationService->handleEditReservation($disponibility, $originalReservation, $reservations, $reservation);

                $entityManagerInterface->persist($disponibility);
                $entityManagerInterface->persist($reservation);
                $entityManagerInterface->flush();

                // $emailService->sendConfirmModifyEmail($reservation->getUser()->getEmail(),$reservation->getDate(),$reservation->getTime(),$reservation->getHowManyGuest());
                $this->addFlash('success', 'La réservation a bien été modifiée');

                return $this->redirectToRoute('app_admin_reservation_crud', ['date' => $dateReservation, 'modifiée' => 1]);
            } catch (\Exception $e) {
                // Afficher les erreurs
                $this->addFlash('warning', $e->getMessage());

                return new Response('Bad Request', 400);
            }
        }

        return $this->render('admin/reservations_crud/edit_reservation.html.twig', [
            'reservationForm' => $reservationForm->createView(),
            'dateReservation' => $dateReservation,
        ]);
    }

    #[Route('/annuler-reservation/{id}', name: 'app_admin_reservation_cancel')] 
    public function cancelReservation(int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManagerInterface, Request $request):Response
    {
        // Récupérer l'entité de la réservation à supprimer
        $reservation = $reservationRepository->find($id);
        // Vérifier si la réservation existe
        if (!$reservation) {
            throw $this->createNotFoundException('La réservation n\'existe pas');
        }

        // Supprimer l'entité de la base de données
        $entityManagerInterface->remove($reservation);
        $entityManagerInterface->flush();

        // Répondre avec une réponse de succès
        $this->addFlash('success', 'La réservation a bien été annulée');

        return $this->redirect($request->headers->get('referer'));
    }
}
