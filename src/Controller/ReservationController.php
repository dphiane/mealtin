<?php

namespace App\Controller;

use App\Entity\Disponibility;
use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use App\Service\EmailService;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    #[IsGranted('ROLE_USER')]
    public function newReservation(ReservationService $reservationService, Request $request, EntityManagerInterface $entityManagerInterface, ReservationRepository $reservationRepository, EmailService $emailService): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $user = $this->getUser();
        $today = new \DateTimeImmutable('now');
        $timeOfToday = $today->format('H:i');

        if($form->isSubmitted()){
            // Si l'utilisateur est mal intentionné et tente d'envoyer une heure qui ne respecte pas la liste d'horaires autorisés
            if($form->getData()->getTime() === null){
                $this->addFlash("warning", "Veuillez respecter les crénaux horaires!");
                return new Response('Bad Request',400);
            }
        }
        if ($form->isSubmitted() && $form->isValid()) {
            
            $date = $reservation->getDate();
            $reservations = $reservationRepository->findOneBy(['date' => $date]);
            try {
                $reservationService->isDateAndTimeValid($reservation->getTime()->format('H:i'), $date, $today, $timeOfToday);
                // si une date possède déja une entity Disponibility
                if ($reservations) {
                    $disponibility = $reservationService->checkDisponibilityAndUpdateEntity($reservations->getDisponibility(), $reservation);
                } else {
                    // Si aucune réservation n'est trouvée, créez une nouvelle disponibilité
                    $disponibility = new Disponibility();
                    $reservationService->handleNewReservation($disponibility,$reservation);
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
                $this->addFlash('success', 'Votre réservation a bien été enregistrée');

                $emailService->sendConfirmNewReservation($reservation->getUser()->getEmail(), $reservation->getDate()->format("d-m-Y"), $reservation->getTime()->format('H:i'), $reservation->getHowManyGuest());

                return $this->redirectToRoute('app_my_reservation', ['success' => 1]);
                
            } catch (\Exception $e) {
                // Gérez les erreurs de manière appropriée
                $this->addFlash('warning', $e->getMessage());
                return new Response($e->getMessage(),400);
            }
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form,
        ]);
    }
}
