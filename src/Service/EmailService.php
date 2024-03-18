<?php

namespace App\Service;

use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{

    public function __construct(
        private MailerInterface $mailer
    ) {
    }
    //il faut modifier le path dans le fichier twig.yaml pour avoir le bon namespace
    public function sendConfirmModifyEmail($recipientEmail, $reservationDate, $reservationTime, $numberOfGuests)
    {
        $email = new TemplatedEmail();
        $email->from(new Address('dphiane@yahoo.fr', 'Mealtin'))
            ->to($recipientEmail)
            ->subject('Confirmation modification de votre réservation restaurant Mealtin')
            ->htmlTemplate('@email_templates/modify.html.twig')
            ->context(
                ['date' => $reservationDate, 'time' => $reservationTime, 'guest' => $numberOfGuests]
            );
        $this->mailer->send($email);
    }

    public function sendConfirmNewReservation($recipientEmail,$reservationDate, $reservationTime,$numberOfGuests){
        $email = new TemplatedEmail();
        $email->from(new Address('dphiane@yahoo.fr', 'Mealtin'))
            ->to($recipientEmail)
            ->subject('Confirmation modification de votre réservation restaurant Mealtin')
            ->htmlTemplate('@email_templates/new_reservation.html.twig')
            ->context(
                ['date' => $reservationDate, 'time' => $reservationTime, 'guest' => $numberOfGuests]
            );
        $this->mailer->send($email);
    }
}
