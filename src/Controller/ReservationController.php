<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\Reservation;
use App\Entity\Disponibility;
use App\Form\ReservationType;
use App\Service\EmailService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    #[IsGranted('ROLE_USER')]
    public function index(ReservationService $reservationService, Request $request, EntityManagerInterface $entityManagerInterface, ReservationRepository $reservationRepository, EmailService $emailService): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $user = $this->getUser();
        $today = new DateTime("now");
        $timeOfToday = $today->format('H:i');
        if ($form->isSubmitted() && $form->isValid()) {
            $date = $reservation->getDate();
            $reservations = $reservationRepository->findOneBy(['date' => $date]);
            try{
                $reservationService->validateTimeAndDate($reservation->getTime()->format("H:i"),$date,$today,$timeOfToday);

                if ($reservations) {
                    //dd($reservations);
                    $disponibility =  $reservationService->handleIfReservationExistAtThisDate($reservations, $reservation);
                } else {
                    
                    // Si aucune réservation n'est trouvée, créez une nouvelle disponibilité
                    $disponibility = new Disponibility();
                    $reservationTime = $reservation->getTime()->format("H:i");
                    $howManyGuest = $reservation->getHowManyGuest();
    
                    if ($reservationTime >= "12:00" && $reservationTime <= "14:00") {
                        $reservationService->newReservationLunch($disponibility,$howManyGuest);
    
                    } elseif ($reservationTime >= "19:00" && $reservationTime <= "21:00") {
                        $reservationService->newReservationDiner($disponibility,$howManyGuest);
    
                    } else {
                        $this->addFlash("warning", "Veuillez entrer un horaire valide !");
                        return new Response("Bad Request", 400);
                    }
                }
    
                // Attribuez cette disponibilité à la réservation créée
                if (!$entityManagerInterface->contains($disponibility)) {
                    $entityManagerInterface->persist($disponibility);
                }
    
                // Associe $disponibility à $reservation (si nécessaire)
                $reservation->setUser($user);
                $reservation->setDisponibility($disponibility);
    
                // Persiste ensuite $reservation
                if (!$entityManagerInterface->contains($reservation)) {
                    $entityManagerInterface->persist($reservation);
                }
                // Flush des changements
                $entityManagerInterface->flush();
                $this->addFlash("success", "Votre réservation a bien été enregistrée");
    
                //$emailService->sendConfirmNewReservation($reservation->getUser()->getEmail(), $reservation->getDate(), $reservation->getTime(), $reservation->getHowManyGuest());
    
                return $this->redirectToRoute('app_my_reservation', ["success" => 1]);

            }catch(Exception $e) {
                // Gérez les erreurs de manière appropriée
                $this->addFlash("warning", $e->getMessage());
                return new Response("Bad Request", 400);
            }
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form,
        ]);
    }
}
