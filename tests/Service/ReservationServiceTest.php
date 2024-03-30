<?php

namespace App\Tests\Service;

use Exception;
use DateTimeImmutable;
use App\Entity\Reservation;
use App\Entity\Disponibility;
use PHPUnit\Framework\TestCase;
use App\Service\ReservationService;
use Doctrine\ORM\EntityManagerInterface;

class ReservationServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private ReservationService $reservationService;
    private Reservation $originalReservation;
    private Disponibility $disponibility;
    private Reservation $reservationAtThisDateToGetDispo;
    private Reservation $modifyReservation;

    // dans le temps ces dates ne seront plus valide suite a la fonction isValidDateAndTime.
    CONST DATE = "2024-04-23";
    CONST NEWDATE = "2024-04-30";
    CONST DATE_WITH_NO_DISPO_CREATED = "2024-05-01";

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->reservationService = new ReservationService($this->entityManager);
        $this->originalReservation = $this->reservation();
        $this->disponibility = $this->disponibility();
        $this->reservationAtThisDateToGetDispo = $this->reservationAtThisDateToGetDispo();
        $this->modifyReservation = $this->reservation();
    }

    public function disponibility()
    {
        $disponibility = new Disponibility();
        $disponibility->setMaxReservationLunch(10);
        $disponibility->setMaxReservationDiner(13);
        $disponibility->setMaxSeatLunch(30);
        $disponibility->setMaxSeatDiner(40);
        return $disponibility;
    }
    
    public function reservation()
    {
        $reservation = new Reservation();
        $reservation->setDate(new DateTimeImmutable(self::DATE));
        $reservation->setDisponibility($this->disponibility());
        $reservation->setHowManyGuest(5);
        $reservation->setTime(new DateTimeImmutable('12:30:00'));
        return $reservation;
    }

    public function reservationAtThisDateToGetDispo()
    {
        $disponibility = new Disponibility();
        $disponibility->setMaxReservationDiner(1)
            ->setMaxReservationLunch(2)
            ->setMaxSeatDiner(5)
            ->setMaxSeatLunch(7);
        $reservationAtThisDateToGetDispo = new Reservation();
        $reservationAtThisDateToGetDispo->setDate(new DateTimeImmutable(self::NEWDATE))
            ->setDisponibility($disponibility);
        return $reservationAtThisDateToGetDispo;
    }

    public function testEditDifferentDayForLunchWithNoDispoCreated(){
        $newDinerTime = new DateTimeImmutable('12:45');
        $newDate = new DateTimeImmutable(self::DATE_WITH_NO_DISPO_CREATED);
        $nullReservation = null;

        $this->modifyReservation->setTime($newDinerTime)
                                ->setDate($newDate);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $nullReservation, $this->modifyReservation);
        $modifiedDisponibility = $this->modifyReservation->getDisponibility();

        $this->assertEquals(13, $modifiedDisponibility->getMaxReservationDiner());
        $this->assertEquals(12, $modifiedDisponibility->getMaxReservationLunch());
        $this->assertEquals(40, $modifiedDisponibility->getMaxSeatDiner());
        $this->assertEquals(35, $modifiedDisponibility->getMaxSeatLunch());
    }
    public function testEditDifferentDayForDinerWithNoDispoCreated(){
        $newDinerTime = new DateTimeImmutable('19:45');
        $newDate = new DateTimeImmutable(self::DATE_WITH_NO_DISPO_CREATED);
        $nullReservation = null;

        $this->modifyReservation->setTime($newDinerTime)
                                ->setDate($newDate);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $nullReservation, $this->modifyReservation);
        $modifiedDisponibility = $this->modifyReservation->getDisponibility();

        $this->assertEquals(12, $modifiedDisponibility->getMaxReservationDiner());
        $this->assertEquals(13, $modifiedDisponibility->getMaxReservationLunch());
        $this->assertEquals(35, $modifiedDisponibility->getMaxSeatDiner());
        $this->assertEquals(40, $modifiedDisponibility->getMaxSeatLunch());
    }

    public function testExceptionDifferentDayNotEnoughtSeatForDiner(){
        $newDinerTime = new DateTimeImmutable('19:45');
        $newDate = $this->reservationAtThisDateToGetDispo()->getDate();
        $this->modifyReservation->setTime($newDinerTime)
                                ->setDate($newDate)
                                ->setHowManyGuest(6);
        
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testExceptionDifferentDayNotEnoughtSeatForLunch(){
        $newDinerTime = new DateTimeImmutable('12:45');
        $newDate = $this->reservationAtThisDateToGetDispo()->getDate();
        $this->modifyReservation->setTime($newDinerTime)
                                ->setDate($newDate)
                                ->setHowManyGuest(8);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testExceptionEditDayBadHourly()
    {
        $newDinerTime = new DateTimeImmutable('18:45');
        $newDate = $this->reservationAtThisDateToGetDispo()->getDate();
        $this->modifyReservation->setDate($newDate)
            ->setTime($newDinerTime);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);

        $newDinerTime = new DateTimeImmutable('11:45');
        $this->modifyReservation->setTime($newDinerTime);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testEditDifferentDayForDinner()
    {
        $newDinerTime = new DateTimeImmutable('19:45');
        $newDate = $this->reservationAtThisDateToGetDispo()->getDate();
        $disponibility = $this->disponibility();
        $this->originalReservation->setDisponibility($disponibility);
        $this->modifyReservation->setDate($newDate)
                                ->setTime($newDinerTime);

        $this->reservationService->handleEditReservation($disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $modifiedDisponibility = $this->modifyReservation->getDisponibility();
        $this->assertEquals(0, $modifiedDisponibility->getMaxSeatDiner());
        $this->assertEquals(0, $modifiedDisponibility->getMaxReservationDiner());
        $this->assertEquals(7, $modifiedDisponibility->getMaxSeatLunch());
        $this->assertEquals(2, $modifiedDisponibility->getMaxReservationLunch());

        $this->assertEquals(35, $disponibility->getMaxSeatLunch());
        $this->assertEquals(11, $disponibility->getMaxReservationLunch());
    }

    public function testEditDifferentDayForLunch()
    {
        $disponibility = $this->disponibility;
        $this->originalReservation->setDisponibility($disponibility);
        $newTime = new DateTimeImmutable('13:00');
        $newDate = $this->reservationAtThisDateToGetDispo()->getDate();
        $this->modifyReservation->setTime($newTime);
        $this->modifyReservation->setDate($newDate);
        $this->reservationService->handleEditReservation($disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $modifiedDisponibility = $this->modifyReservation->getDisponibility();
        $this->assertEquals(1, $modifiedDisponibility->getMaxReservationLunch());
        $this->assertEquals(2, $modifiedDisponibility->getMaxSeatLunch());
        $this->assertEquals(5, $modifiedDisponibility->getMaxSeatDiner());
        $this->assertEquals(1, $modifiedDisponibility->getMaxReservationDiner());

        $this->assertEquals(35, $disponibility->getMaxSeatLunch());
        $this->assertEquals(11, $disponibility->getMaxReservationLunch());
    }

    public function testExceptionNotEnoughtSeatSameDayForDiner()
    {
        $dinerTime = new DateTimeImmutable('19:45');
        $this->modifyReservation->setTime($dinerTime);
        $this->disponibility->setMaxSeatDiner(2);
        $this->modifyReservation->setHowManyGuest(9);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testExceptionNotEnoughtSeatSameDayForLunch()
    {
        $this->disponibility->setMaxSeatLunch(2);
        $this->modifyReservation->setHowManyGuest(9);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testExceptionEditSameDayWithBadHourly()
    {
        $badHourly = new DateTimeImmutable('21:45');
        $this->modifyReservation->setTime($badHourly);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testExceptionEditSameDayForDiner()
    {
        $dinerTime = new DateTimeImmutable('19:45');
        $this->disponibility->setMaxSeatDiner(3);
        $this->modifyReservation->setTime($dinerTime);
        $this->modifyReservation->setHowManyGuest(10);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testExceptionEditSameDayForLunch()
    {
        $this->disponibility->setMaxSeatLunch(3);
        $this->modifyReservation->setHowManyGuest(10);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testEditSameDayForDiner()
    {
        $this->disponibility->setMaxSeatDiner(35);
        $dinerTime = new DateTimeImmutable("20:30");
        $this->originalReservation->setTime($dinerTime);
        $newDinerTime = new DateTimeImmutable("20:00");
        $this->modifyReservation->setTime($newDinerTime);
        $this->modifyReservation->setHowManyGuest(2);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $this->assertEquals(38, $this->disponibility->getMaxSeatDiner());
        $this->assertEquals(2, $this->modifyReservation->getHowManyGuest());
    }

    public function testEditSameDayForLunch()
    {
        $newTime = new DateTimeImmutable('13:30');
        $this->modifyReservation->setHowManyGuest(6);
        $this->modifyReservation->setTime($newTime);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $this->assertEquals(6, $this->modifyReservation->getHowManyGuest());
        $this->assertEquals(29, $this->disponibility->getMaxSeatLunch());
    }

    public function testNotEnoughtSeatForLunchWhenMoveDinerToLunch()
    {
        $dinerTime = new DateTimeImmutable("19:30");
        $this->originalReservation->setTime($dinerTime);
        $newTime = new DateTimeImmutable("13:30");
        $this->modifyReservation->setTime($newTime);
        $this->disponibility->setMaxSeatLunch(0);
        $this->modifyReservation->setHowManyGuest(5);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testMoveDinerToLunchSameDay()
    {
        $dinerTime = new DateTimeImmutable("19:45");
        $this->originalReservation->setTime($dinerTime);
        $newTime = new DateTimeImmutable('13:15');
        $date = new DateTimeImmutable(self::DATE);
        $this->modifyReservation->setTime($newTime);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $this->assertEquals($date, $this->modifyReservation->getDate());
        $this->assertEquals($newTime, $this->modifyReservation->getTime());
    }

    public function testNotEnoughtSeatForDinerWhenMoveLunchToDiner()
    {
        $newTime = new DateTimeImmutable("19:30");
        $this->modifyReservation->setTime($newTime);
        $this->disponibility->setMaxSeatDiner(0);
        $this->modifyReservation->setHowManyGuest(4);
        $this->expectException(Exception::class);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
    }

    public function testMoveLunchToDinerSameDay()
    {
        $newTime = new DateTimeImmutable("19:30");
        $date = new DateTimeImmutable(self::DATE);
        $this->modifyReservation->setTime($newTime);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $this->assertEquals($newTime, $this->modifyReservation->getTime());
        $this->assertEquals($date, $this->modifyReservation->getDate());
        $this->assertEquals(12, $this->disponibility->getMaxReservationDiner());
        $this->assertEquals(11, $this->disponibility->getMaxReservationLunch());
        $this->assertEquals(35, $this->disponibility->getMaxSeatDiner());
        $this->assertEquals(35, $this->disponibility->getMaxSeatLunch());
    }

    public function testChangeNumberGuestSameDay()
    {   
        $this->modifyReservation->setHowManyGuest(9);
        $this->reservationService->handleEditReservation($this->disponibility, $this->originalReservation, $this->reservationAtThisDateToGetDispo, $this->modifyReservation);
        $this->assertEquals(9, $this->modifyReservation->getHowManyGuest());
        $this->assertNotEquals(5, $this->modifyReservation->getHowManyGuest());
        $this->assertEquals(new DateTimeImmutable(self::DATE), $this->modifyReservation->getDate());
    }

    public function testHandleNewReservation()
    {
        $disponibility = new Disponibility();
        $reservation = new Reservation();
        $reservation->setTime(new DateTimeImmutable('19:00'));
        $reservation->setHowManyGuest(15);

        $this->reservationService->handleNewReservation($disponibility, $reservation);
        $this->assertEquals(25, $disponibility->getMaxSeatDiner());
        $this->assertEquals(12, $disponibility->getMaxReservationDiner());
        $this->assertEquals(13, $disponibility->getMaxReservationLunch());
        $this->assertEquals(40, $disponibility->getMaxSeatLunch());
    }

    public function testCreateNewReservationLunch()
    {
        $disponibility = new Disponibility();
        $howManyGuest = 9;

        $this->reservationService->createNewReservationLunch($disponibility, $howManyGuest);
        $this->assertEquals(12, $disponibility->getMaxReservationLunch());
        $this->assertEquals(13, $disponibility->getMaxReservationDiner());
        $this->assertEquals(31, $disponibility->getMaxSeatLunch());
        $this->assertEquals(40, $disponibility->getMaxSeatDiner());
    }

    public function testCreateNewReservationDiner()
    {
        $disponibility = new Disponibility();
        $howManyGuest = 5;

        $this->reservationService->createNewReservationDiner($disponibility, $howManyGuest);
        $this->assertEquals(35, $disponibility->getMaxSeatDiner());
        $this->assertEquals(40, $disponibility->getMaxSeatLunch());
        $this->assertEquals(13, $disponibility->getMaxReservationLunch());
        $this->assertEquals(12, $disponibility->getMaxReservationDiner());
    }

    public function testIsDateAndTimeValid()
    {
        $date = new DateTimeImmutable('2024-03-28');
        $today = new DateTimeImmutable('2024-03-27');

        $result =  $this->reservationService->isDateAndTimeValid('12:00', $date, $today, '16:02');
        $this->assertTrue($result);
    }

    public function testIsTodayDateAndTimeValid()
    {
        $date = new DateTimeImmutable('2024-03-27');
        $today = new DateTimeImmutable('now');

        $this->expectException(Exception::class);
        $result =  $this->reservationService->isDateAndTimeValid('12:00', $date, $today, '16:02');
    }

    public function testCheckDisponibilityAndUpdateEntity()
    {
        $disponibility = new Disponibility();
        $disponibility->setMaxReservationLunch(10);
        $disponibility->setMaxReservationDiner(10);
        $disponibility->setMaxSeatDiner(40);
        $disponibility->setMaxSeatLunch(40);

        $reservation = new Reservation();
        $reservation->setTime(new DateTimeImmutable('12:30')); // Heure de réservation valide
        $reservation->setHowManyGuest(4); // Nombre d'invités valide

        // Vérifier que la disponibilité est mise à jour correctement
        $updatedDisponibility = $this->reservationService->checkDisponibilityAndUpdateEntity($disponibility, $reservation);
        // Vérifier que la disponibilité a été mise à jour correctement pour le déjeuner
        $this->assertEquals(9, $updatedDisponibility->getMaxReservationLunch());
        $this->assertEquals(36, $updatedDisponibility->getMaxSeatLunch());

        // Créer une autre instance de Reservation avec un nombre d'invités trop élevé
        $reservationTooManyGuests = new Reservation();
        $reservationTooManyGuests->setTime(new DateTimeImmutable('19:30')); // Heure de réservation valide pour le dîner
        $reservationTooManyGuests->setHowManyGuest(50); // Nombre d'invités invalide

        // Vérifier que l'exception est lancée pour un nombre d'invités trop élevé
        $this->expectException(\Exception::class);
        $this->reservationService->checkDisponibilityAndUpdateEntity($disponibility, $reservationTooManyGuests);

        // Créer une autre instance de Reservation avec une heure de réservation invalide
        $reservationInvalidTime = new Reservation();
        $reservationInvalidTime->setTime(new DateTimeImmutable('10:00')); // Heure de réservation invalide
        $reservationInvalidTime->setHowManyGuest(4); // Nombre d'invités valide

        // Vérifier que l'exception est lancée pour une heure de réservation invalide
        $this->expectException(\Exception::class);
        $this->reservationService->checkDisponibilityAndUpdateEntity($disponibility, $reservationInvalidTime);
    }

}
