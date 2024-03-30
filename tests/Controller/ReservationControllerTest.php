<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationControllerTest extends WebTestCase
{
    /**
     * @var null|\App\Entity\User
     */
    private $user;

    public function testReservationRouteIfNotLogin()
    {
        // Créer un client HTTP Symfony
        $client = static::createClient();
        $client->request("GET", "/reservation");
        $this->assertResponseRedirects();
    }

    public function testReservationRouteIfLogged()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        // Récupérer l'utilisateur de test
        $this->user = $userRepository->findOneByEmail('eugene14@maillet.fr');
        $client->loginUser($this->user);
        // Tester par exemple la page de profil
        $client->request('GET', '/reservation');
        $this->assertResponseIsSuccessful();
    }

    public function testReservationFormDisplay(): void
    {
        // Crée un client HTTP Symfony
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        // Récupérer l'utilisateur de test
        $this->user = $userRepository->findOneByEmail('eugene14@maillet.fr');
        // Simuler la connexion de l'utilisateur
        $client->loginUser($this->user);

        // Effectue une requête HTTP GET pour accéder à la page de réservation
        $client->request('GET', '/reservation');

        // Vérifie si la page est accessible et le code de réponse HTTP est 200 (OK)
        $this->assertResponseIsSuccessful();

        // Vérifie si le formulaire de réservation est affiché
        $this->assertSelectorExists('form[name="reservation"]');
    }

/*     public function testValidFormSubmit()
    {
        // Crée un client HTTP Symfony
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        // Récupérer l'utilisateur de test
        $this->user = $userRepository->findOneByEmail('eugene14@maillet.fr');
        // Simuler la connexion de l'utilisateur
        $client->loginUser($this->user);
        $crawler = $client->request('GET','/reservation');
        $submitButton = $crawler->selectButton('Réserver');

        $date = new DateTimeImmutable('2024-03-27 19:00:00');
        $form= $submitButton->form();
        $form["reservation[date]"] = 
        $form["reservation[time][hour]"] = $date->format('H');
        $form["reservation[time][minute]"] = $date->format('i');
        $form["reservation[howManyGuest]"] = '5';

        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('reservation')->getValue();
        $session = $client->getContainer()->get('session');
        $session->set('your_session_key', 'your_session_value');
        $session->save();
        $form['reservation[_token]'] = $token;
        $client->submit($form);
        $this->assertResponseStatusCodeSame(302);
    } */
}
