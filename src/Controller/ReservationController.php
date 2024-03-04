<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Disponibility;
use App\Form\ReservationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
use DateTime;
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
        $today = new DateTime("now");

        if ($form->isSubmitted() && $form->isValid()) {
            $date = $reservation->getDate();
            $reservations = $reservationRepository->findOneBy(['date' => $date]);

            if($date < $today->modify("-1 day")  || $date > $today->modify('+1 month +2 weeks') ){
                $this->addFlash("warning","votre réservation ne peut dépasser aller plus loin que 1 mois et 2 semaines");
                return new Response("Bad Request", 400);
            }
            
            if ($reservations) {    
            $disponibility=  $reservationService->ifReservation($reservations,$reservation,$request);
            } else {
                // Si aucune réservation n'est trouvée, créez une nouvelle disponibilité
                $disponibility = new Disponibility();
                $reservationTime = $reservation->getTime()->format("H:i");
                $howManyGuest = $reservation->getHowManyGuest();
                if($reservationTime >= "12:00" && $reservationTime <= "14:00" ){
                    $disponibility
                    ->setMaxReservationLunch(12)
                    ->setMaxSeatLunch(40 - $howManyGuest)
                    ->setMaxReservationDiner(13)
                    ->setMaxSeatDiner(40);
                }elseif($reservationTime >= "19:00" && $reservationTime <= "21:00"){
                    $disponibility
                    ->setMaxReservationDiner(12)
                    ->setMaxSeatDiner(40 - $howManyGuest)
                    ->setMaxReservationLunch(13)
                    ->setMaxSeatLunch(40);
                }else{
                    $this->addFlash("warning","Veuillez entrer un horaire valide !");
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

            /*             $email = new TemplatedEmail();
            $email->from(new Address('dphiane@yahoo.fr', 'Dominique'))
                ->to($reservation->getUser()->getEmail())
                ->subject('Confirmation réservation restaurant Mealtin\'Potes')
                ->html($this->renderView(
                    'reservation/confirmation_email.html.twig',
                    ['date' => $reservation->getDate(), 'time' => $reservation->getTime(), 'guest' => $reservation->getHowManyGuest()]
                ));
            $mailerInterface->send($email); */
            
            return $this->redirectToRoute('app_my_reservation');
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form,
        ]);
    }
}
