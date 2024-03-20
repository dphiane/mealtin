<?php

namespace App\Controller\Admin;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\Criteria;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    #[IsGranted('ROLE_ADMIN', message: 'You are not allowed to access the admin dashboard.')]
    public function index(ReservationRepository $reservationRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $today = new \DateTimeImmutable();

        // Créez un objet Criteria pour ajouter des conditions à la requête
        $criteria = Criteria::create()
            ->where(Criteria::expr()->gte('date', $today));
        $reservations = $reservationRepository->matching($criteria);
        $reservationsDay = [];

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
        //dd($reservationsPerMonth);
        return $this->render('admin/index.html.twig', [
            'reservationsPerMonth' => $reservationsPerMonth
        ]);
    }
}
