<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationFormTest extends WebTestCase
{    
    public function testIndexFormSubmissionSuccess()
    {
        $client = $this->createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('eugene14@maillet.fr');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/reservation');

        $form = $crawler->selectButton('Réserver')->form();
        $form['reservation[date]'] = '2024-04-10'; // Remplacez cette valeur par une date valide
        $form['reservation[time][hour]'] = '12'; // Remplacez cette valeur par une heure valide
        $form['reservation[time][minute]'] = '15'; // Remplacez cette valeur par une heure valide
        $form['reservation[howManyGuest]'] = 4; // Remplacez cette valeur par le nombre d'invités valide

        $client->submit($form);

        // Vérifiez si la redirection s'est bien effectuée vers la page 'app_my_reservation'
        $this->assertTrue($client->getResponse()->isRedirect('/mes-reservations?success=1'));

        // Suivez la redirection
        $client->followRedirect();
        $this->assertAnySelectorTextContains('.flash-success','Votre réservation a bien été enregistrée');
    }

    public function testIndexFormSubmissionInvalidData()
    {
        $client = $this->createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByEmail('eugene14@maillet.fr');
        $client->loginUser($user);
        $crawler = $client->request('GET', '/reservation');

        $form = $crawler->selectButton('Réserver')->form();
        // Ne pas remplir le formulaire avec des données valides
        $form['reservation[date]'] = '2024-04-01';
        $form['reservation[time][hour]'] = '12'; // Remplacez cette valeur par une heure valide
        $form['reservation[time][minute]'] = '15'; // Remplacez cette valeur par une heure valide
        $form['reservation[howManyGuest]'] = '4';

        $client->submit($form);

        $this->assertResponseStatusCodeSame(400);
        $this->assertRouteSame('app_reservation');
    }
}
