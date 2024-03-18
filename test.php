<?php

#[Route('/modifier-reservation/{id}', name: 'app_admin_reservation_edit')]
public function editReservation($id, ReservationRepository $reservationRepository, Request $request, DisponibilityRepository $disponibilityRepository, EntityManagerInterface $entityManagerInterface, EmailService $emailService): Response
{
    // Extraction des données de la requête
    [$reservation, $disponibility, $dateReservation, $reservationForm, $reservationHowManyGuest, $reservationTime, $disponibilityMaxSeatLunch, $disponibilityMaxReservationLunch, $disponibilityMaxSeatDiner, $disponibilityMaxReservationDiner, $newReservationTimeFormated, $newReservationHowManyGuest, $oldDate, $newDate, $today, $timeOfToday] = $this->extractData($id, $reservationRepository, $disponibilityRepository, $request);

    // Validation de la réservation
    if (!$this->validateReservation($reservation, $disponibility, $reservationForm, $newReservationTimeFormated, $newReservationHowManyGuest, $oldDate, $newDate, $today, $timeOfToday)) {
        return new Response("Bad Request", 400);
    }

    // Modification de la réservation
    $this->modifyReservation($reservation, $disponibility, $reservationHowManyGuest, $reservationTime, $newReservationTimeFormated, $newReservationHowManyGuest, $disponibilityMaxSeatLunch, $disponibilityMaxReservationLunch, $disponibilityMaxSeatDiner, $disponibilityMaxReservationDiner);

    // Envoi de l'e-mail de confirmation
    $this->sendConfirmationEmail($reservation, $emailService);

    return $this->redirectToRoute('app_admin_reservation_crud', ['date' => $dateReservation, 'modifiée' => 1]);
}

private function extractData($id, ReservationRepository $reservationRepository, DisponibilityRepository $disponibilityRepository, Request $request): array
{
    $reservation = $reservationRepository->findOneBy(['id' => $id]);
    $disponibility = $disponibilityRepository->findOneBy(['id' => $reservation->getDisponibility()]);
    $dateReservation = $reservation->getDate()->format('Y-m-d');
    $reservationForm = $this->createForm(ReservationType::class, $reservation);
    $reservationForm->handleRequest($request);
    $newReservationTimeFormated = $reservationForm->getData()->getTime()->format('H:i');
    $newReservationHowManyGuest = $reservationForm->getData()->getHowManyGuest();
    $oldDate = $reservation->getDate();
    $newDate = $reservationForm->getData()->getDate();
    $today = new DateTime("now");
    $timeOfToday = $today->format('H:i');

    return [$reservation, $disponibility, $dateReservation, $reservationForm, $reservation->getHowManyGuest(), $reservation->getTime()->format("H:i"), $disponibility->getMaxSeatLunch(), $disponibility->getMaxReservationLunch(), $disponibility->getMaxSeatDiner(), $disponibility->getMaxReservationDiner(), $newReservationTimeFormated, $newReservationHowManyGuest, $oldDate, $newDate, $today, $timeOfToday];
}

private function validateReservation($reservation, $disponibility, $reservationForm, $newReservationTimeFormated, $newReservationHowManyGuest, $oldDate, $newDate, $today, $timeOfToday): bool
{
    if ($newReservationTimeFormated < $timeOfToday && $newDate->format("Y-m-d") < $today->format("Y-m-d")) {
        $this->addFlash("warning", "Votre réservation ne peut être inférieure à maintenant");
        return false;
    }
    if ($newDate > $today->modify('+1 month +2 weeks')) {
        $this->addFlash("warning", "Votre réservation ne peut dépasser 1 mois et 2 semaines à partir de maintenant");
        return false;
    }

    if ($oldDate != $newDate) {
        // Logique de validation supplémentaire si nécessaire
    }

    return true;
}

private function modifyReservation($reservation, $disponibility, $reservationHowManyGuest, $reservationTime, $newReservationTimeFormated, $newReservationHowManyGuest, $disponibilityMaxSeatLunch, $disponibilityMaxReservationLunch, $disponibilityMaxSeatDiner, $disponibilityMaxReservationDiner): void
{
    // Logique de modification de la réservation
}

private function sendConfirmationEmail($reservation, $emailService): void
{
    $emailService->sendConfirmModifyEmail($reservation->getUser()->getEmail(), $reservation->getDate(), $reservation->getTime(), $reservation->getHowManyGuest());
}
