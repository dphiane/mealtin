<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Disponibility;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
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
    public function index(ReservationService $reservationService,Request $request, EntityManagerInterface $entityManagerInterface, ReservationRepository $reservationRepository, MailerInterface $mailerInterface): Response
    {   
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $reservation->getDate();
            $reservations = $reservationRepository->findOneBy(['date' => $date]);

            if ($reservations) {    
            $disponibility=  $reservationService->ifReservation($reservations,$reservation);
            } else {
                // Si aucune réservation n'est trouvée, créez une nouvelle disponibilité
                $disponibility = new Disponibility();
                $disponibility->setMaxReservationDiner(13)
                    ->setMaxSeatLunch(40)
                    ->setMaxReservationLunch(13)
                    ->setMaxSeatDiner(40);
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
