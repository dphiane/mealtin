<?php

namespace App\Tests\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
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

}
