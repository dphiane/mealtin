<?php

namespace App\Tests;

use App\Entity\Reservation;
use App\Form\RegisterType;
use Symfony\Component\Form\Test\TypeTestCase;

/* class ReservationFormTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = [
            'date' => '2024-03-27',
            'time' => ['hour' => 12, 'minute' => 0],
            'howManyGuest' => 5,
        ];

        $expectedData = [
            'date' => new \DateTimeImmutable('2024-03-27'),
            'time' => new \DateTimeImmutable('12:00:00'),
            'howManyGuest' => 5,
        ];

        $reservation = new Reservation();
        // $reservation will retrieve data from the form submission; pass it as the second argument
        $form = $this->factory->create(RegisterType::class, $reservation);

        $expected = new Reservation();
        // ...populate $expected properties with the data stored in $formData

        // submit the data to the form directly
        $form->submit($formData);

        // This check ensures there are no transformation failures
        $this->assertTrue($form->isSynchronized());

        // check that $reservation was modified as expected when the form was submitted
        $this->assertEquals($expectedData, $reservation);
    }

    public function testCustomFormView(): void
    {
        $formData = new TestObject();
        // ... prepare the data as you need

        // The initial data may be used to compute custom view variables
        $view = $this->factory->create(TestedType::class, $formData)
            ->createView();

        $this->assertArrayHasKey('custom_var', $view->vars);
        $this->assertSame('expected value', $view->vars['custom_var']);
    }
} */
