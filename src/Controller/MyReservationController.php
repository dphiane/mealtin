<?php

namespace App\Controller;

use DateTime;
use App\Form\ReservationType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\DisponibilityRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
    public function editReservation($id, ReservationRepository $reservationRepository, Request $request, DisponibilityRepository $disponibilityRepository, EntityManagerInterface $entityManagerInterface, MailerInterface $mailerInterface)
    {
        $reservation = $reservationRepository->findOneBy(['id' => $id]);
        $disponibility = $disponibilityRepository->findOneBy(['id' => $reservation->getDisponibility()]);
        $dateReservation = $reservation->getDate()->format('Y-m-d');
        $reservationHowManyGuest = $reservation->getHowManyGuest();
        $reservationTime = $reservation->getTime()->format("H:i");

        $disponibilityMaxSeatDiner = $disponibility->getMaxSeatDiner();
        $disponibilityMaxSeatLunch = $disponibility->getMaxSeatLunch();
        $disponibilityMaxReservationLunch = $disponibility->getMaxReservationLunch();
        $disponibilityMaxReservationDiner = $disponibility->getMaxReservationDiner();

        $reservationForm = $this->createForm(ReservationType::class, $reservation);
        $reservationForm->handleRequest($request);
        $newReservationTimeFormated = $reservationForm->getData()->getTime()->format('H:i');
        $newReservationHowManyGuest = $reservationForm->getData()->getHowManyGuest();
        $oldDate = $reservation->getDate();
        $newDate = $reservationForm->getData()->getDate();
        $today = new DateTime("now");

        if ($reservationForm->isSubmitted() && $reservationForm->isValid()) {

            if ($newDate < $today  || $newDate > $today->modify('+1 month +2 weeks')) {
                $this->addFlash("warning", "votre réservation ne peut dépasser aller plus loin que 1 mois et 2 semaines");
                return new Response("Bad Request", 400);
            }
            //si le jour n'est pas changé
            if ($oldDate ==  $newDate) {
                //réservation du midi vers le soir
                if ($reservationTime >= "12:00" && $reservationTime <= "14:00" && $newReservationTimeFormated >= "19:00" && $newReservationTimeFormated <= "21:00") {

                    if ($disponibilityMaxSeatDiner - $newReservationHowManyGuest >= 0) {

                        $reservation->setHowManyGuest($newReservationHowManyGuest);
                        $disponibility
                            ->setMaxReservationLunch($disponibilityMaxReservationLunch + 1)
                            ->setMaxReservationDiner($disponibilityMaxReservationDiner - 1)
                            ->setMaxSeatLunch($disponibilityMaxSeatLunch + $reservationHowManyGuest)
                            ->setMaxSeatDiner($disponibilityMaxSeatDiner - $newReservationHowManyGuest);
                    } else {
                        $this->addFlash("warning", "Malheuresement nous n'avons plus assez de place pour le diner");
                        return new Response("Bad Request", 400);
                    }
                    //réservation du soir vers le midi
                } elseif ($reservationTime >= "19:00" && $reservationTime <= "21:00" && $newReservationTimeFormated >= "12:00" && $newReservationTimeFormated <= "14:00") {

                    if ($disponibilityMaxSeatLunch - $newReservationHowManyGuest >= 0) {
                        $reservation->setHowManyGuest($newReservationHowManyGuest);
                        $disponibility
                            ->setMaxReservationDiner($disponibilityMaxReservationDiner + 1)
                            ->setMaxReservationLunch($disponibilityMaxReservationLunch - 1)
                            ->setMaxSeatDiner($disponibilityMaxSeatDiner + $reservationHowManyGuest)
                            ->setMaxSeatLunch($disponibilityMaxSeatLunch - $newReservationHowManyGuest);
                    } else {
                        $this->addFlash("warning", "Malheuresement nous n'avons plus assez de place pour le midi");
                        return new Response("Bad Request", 400);
                    }
                } else {
                    // réservation du midi inchangé

                    if ($newReservationTimeFormated >= "12:00" && $newReservationTimeFormated <= "14:00") {
                        if ($disponibilityMaxSeatLunch - ($newReservationHowManyGuest - $reservationHowManyGuest) >= 0) {
                            $disponibility->setMaxSeatLunch($disponibilityMaxSeatLunch - ($newReservationHowManyGuest - $reservationHowManyGuest));
                            $reservation->setHowManyGuest($newReservationHowManyGuest);
                        } else {
                            $this->addFlash("warning", "Malheuresement nous n'avons plus assez de place");
                            return new Response("Bad Request", 400);
                        }
                        //réservation du soir non changé
                    } elseif ($newReservationTimeFormated >= "19:00" && $newReservationTimeFormated <= "21:00") {
                        if ($disponibilityMaxSeatDiner - ($newReservationHowManyGuest - $reservationHowManyGuest) >= 0) {
                            $disponibility->setMaxSeatDiner($disponibilityMaxSeatDiner - ($newReservationHowManyGuest - $reservationHowManyGuest));
                            $reservation->setHowManyGuest($newReservationHowManyGuest);
                        } else {
                            $this->addFlash("warning", "Malheuresement nous n'avons plus assez de place");
                            return new Response("Bad Request", 400);
                        }
                        // le petit malin a contourner le front!  
                    } else {
                        $this->addFlash("warning", "Veuillez respecter les crénaux horaires!");
                        return new Response("Bad Request", 400);
                    }
                }
            }
            $entityManagerInterface->persist($disponibility);
            $entityManagerInterface->persist($reservation);
            $entityManagerInterface->flush();

            $email = new TemplatedEmail();
            $email->from(new Address('dphiane@yahoo.fr', 'Mealtin\'Potes'))
                ->to($reservation->getUser()->getEmail())
                ->subject('Confirmation modification de votre réservation restaurant Mealtin\'Potes')
                ->html($this->renderView(
                    'my_reservation/confirmation_email.html.twig',
                    ['date' => $reservation->getDate(), 'time' => $reservation->getTime(), 'guest' => $reservation->getHowManyGuest()]
                ));
            $mailerInterface->send($email);
            $this->addFlash("success", "Votre réservation a bien été modifiée");

            return $this->redirectToRoute('app_my_reservation', ['modifiée' => 1]);
        }

        return $this->render('my_reservation/edit.html.twig', [
            'reservationForm' => $reservationForm->createView(),
            'dateReservation' => $dateReservation,
            'reservationTime' => $reservationTime
        ]);
    }
    #[Route('/annuler-reservation/{id}', name: 'app_my_reservation_cancel')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function cancelReservation($id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManagerInterface)
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
        $this->addFlash('success', 'Votre réservation a bien été annulée');
        return $this->redirectToRoute("app_my_reservation");
    }
}
