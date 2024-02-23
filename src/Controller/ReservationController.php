<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Disponibility;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    #[Route('/reservation', name: 'app_reservation')]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, EntityManagerInterface $entityManagerInterface, ReservationRepository $reservationRepository, MailerInterface $mailerInterface): Response
    {   
        
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $reservation->getDate();
            $reservations = $reservationRepository->findOneBy(['date' => $date]);

            if ($reservations) {    
                $disponibility = $reservations->getDisponibility();
                $reservationTime = $reservation->getTime()->format("H:i:s");

                $disponibilityReservationLunch = $disponibility->getMaxReservationLunch();
                $disponibilityReservationDiner = $disponibility->getMaxReservationDiner();
                $disponibilityMaxSeatDiner = $disponibility->getMaxSeatDiner();
                $disponibilityMaxSeatLunch = $disponibility->getMaxSeatLunch();

                $howManyGuest= $reservation->getHowManyGuest();
                if ($reservationTime < "14:00:00") {
                    if($disponibilityMaxSeatLunch - $howManyGuest < 0 || $disponibilityReservationLunch - 1 <0){
                        $this->addFlash('warning','Malheursement nous n\'avons pas assez de place');
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
            } else {
                // Si aucune réservation n'est trouvée, créez une nouvelle disponibilité
                $disponibility = new Disponibility();
                $disponibility->setMaxReservationDiner(13)
                    ->setMaxSeatLunch(40)
                    ->setMaxReservationLunch(13)
                    ->setMaxSeatDiner(40);
            }
            $this->addFlash("success", "Votre réservation a bien été enregistrée");

            // Attribuez cette disponibilité à la réservation créée
            $reservation->setDisponibility($disponibility);
            $entityManagerInterface->persist($disponibility);
            $entityManagerInterface->flush();
            $reservation->setUser($user);
            $reservation->setDisponibility($disponibility);
            $entityManagerInterface->persist($reservation);
            $entityManagerInterface->flush();

            /*             $email = new TemplatedEmail();
            $email->from(new Address('dphiane@yahoo.fr', 'Dominique'))
                ->to($reservation->getUser()->getEmail())
                ->subject('Confirmation réservation restaurant Mealtin\'Potes')
                ->html($this->renderView(
                    'reservation/confirmation_email.html.twig',
                    ['date' => $reservation->getDate(), 'time' => $reservation->getTime(), 'guest' => $reservation->getHowManyGuest()]
                ));
            $mailerInterface->send($email); */

            return $this->redirectToRoute('app_home');
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form,
        ]);
    }
}
