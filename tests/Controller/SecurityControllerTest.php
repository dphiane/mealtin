<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginWithCorrectCredential(): void
    {
        $client = static::createClient();

        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('eugene14@maillet.fr');

        $client->loginUser($testUser);

        // user is now logged in, so you can test protected resources
        $client->request('GET', '/mon-profile');
        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithBadCredential(){
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Connection')->form([
            '_username' => 'badEmail@domain.fr',
            '_password' => 'fakePassword'
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }
}
