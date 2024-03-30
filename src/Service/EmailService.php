<?php

namespace App\Service;

use Exception;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class EmailService
{
    private string $senderEmail = 'dphiane@yahoo.fr';
    private string $senderName = 'Mealtin';

    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    // il faut modifier le path dans le fichier twig.yaml pour avoir le bon namespace
    public function sendConfirmModifyEmail(string $recipientEmail, string $reservationDate, string $reservationTime, int $numberOfGuests):void
    {
        $this->sendEmail($recipientEmail, 'Confirmation modification de votre réservation restaurant Mealtin', '@email_templates/modify.html.twig', [
            'date' => $reservationDate,
            'time' => $reservationTime,
            'guest' => $numberOfGuests
        ]);
    }

    public function sendConfirmNewReservation(string $recipientEmail, string $reservationDate, string $reservationTime, int $numberOfGuests):void 
    {
        $this->sendEmail($recipientEmail, 'Confirmation de votre nouvelle réservation restaurant Mealtin', '@email_templates/new_reservation.html.twig', [
            'date' => $reservationDate,
            'time' => $reservationTime,
            'guest' => $numberOfGuests
        ]);
    }

    private function sendEmail(string $recipientEmail, string $subject, string $template, mixed $context):void
    {
        $email = new TemplatedEmail();
        $email->from(new Address($this->senderEmail, $this->senderName))
            ->to($recipientEmail)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);

        try {
            $this->mailer->send($email);
        } catch (Exception $e) {
            throw new Exception($e);
        }
    }
}

