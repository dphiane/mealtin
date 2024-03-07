<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        // get or create the user somehow (e.g. creating some users only
        // for tests while loading the test fixtures)
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('azerty@azerty.com');

        $client->loginUser($testUser);

        // user is now logged in, so you can test protected resources
        $client->request('GET', '/mon-profile');
        $this->assertResponseIsSuccessful();
    }
}
