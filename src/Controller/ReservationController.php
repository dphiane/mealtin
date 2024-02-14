<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationType;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReservationController extends AbstractController
{
    public function __construct(MailerInterface $mailer)
    {        
    }
    
    #[Route('/reservation', name: 'app_reservation')]
    public function index(Request $request, EntityManagerInterface $entityManagerInterface, MailerInterface $mailerInterface): Response
    {
        
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
        $user=$this->getUser();

        if($user==null && $form->isSubmitted()){
            $this->addFlash('warning','Veuillez vous connecter ou vous inscrire pour réserver une table');
            $this->redirectToRoute('app_register');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->setUser($user);
            $entityManagerInterface->persist($reservation);
            $entityManagerInterface->flush();

            $this->addFlash('success','Votre réservation a bien été prise en compte.<br>Un email de confirmation a été envoyé');
            
            $email = new TemplatedEmail();
            $email->from(new Address('dphiane@yahoo.fr', 'Dominique'))
                    ->to($user->getUserIdentifier())
                    ->subject('Confirmation réservation restaurant Mealtin\'Potes')
                    ->html($this->renderView(
                    'reservation/confirmation_email.html.twig',
                    ['date' => $reservation->getDate(),'time'=>$reservation->getTime(),'guest'=>$reservation->getNumberOfGuest()]
                    ));
            $mailerInterface->send($email);
            
            $this->redirectToRoute('app_home');
        }

        return $this->render('reservation/index.html.twig', [
            'form' => $form,
        ]);
    }
}
