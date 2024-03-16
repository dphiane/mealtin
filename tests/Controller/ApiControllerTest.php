<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testCallApiAndReturnJsonResponse()
    {
        $client = static::createClient();
        $client->request('GET', '/api');
        $response = $client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testDisponibilityFromDatabaseWithoutData()
    {

        $client = static::createClient();

        // Création d'une requête GET avec une date factice
        $client->request('GET', '/api', ['date' => '2024-03-10']);
        $response = $client->getResponse();

        // Vérifier que la réponse a un code de statut 200 (OK)
        $this->assertSame(200, $response->getStatusCode());

        // Vérifier que la réponse est au format JSON
        $content = json_decode($response->getContent(), true);
        // Vérifier les données renvoyées sont vide dans la réponse
        $this->assertEmpty($content);

        /*         $this->assertArrayHasKey('maxReservationLunch', $content);
        $this->assertArrayHasKey('maxReservationDiner', $content);
        $this->assertArrayHasKey('maxSeatLunch', $content);
        $this->assertArrayHasKey('maxSeatDiner', $content);
        $this->assertArrayHasKey('hour', $content);
        $this->assertArrayHasKey('minute', $content);
        $this->assertArrayHasKey('howManyGuest', $content);
        $this->assertArrayHasKey('date', $content);
 */
        // Vous pouvez effectuer d'autres assertions pour vérifier les valeurs spécifiques des clés

        // Par exemple :
        /*         $this->assertEquals(4, $content['maxReservationLunch']);
        $this->assertEquals(6, $content['maxSeatDiner']); */
        // etc.
    }
    public function testDisponibilityFromDatabaseWithData(){
        
        $client = static::createClient();

        // Création d'une requête GET avec une date factice
        $client->request('GET', '/api', ['date' => '2024-03-10']);
        $response = $client->getResponse();

        // Vérifier que la réponse a un code de statut 200 (OK)
        $this->assertSame(200, $response->getStatusCode());

        // Vérifier que la réponse est au format JSON
        $content = json_decode($response->getContent(), true);
        // Vérifier les données renvoyées sont vide dans la réponse

        $this->assertArrayHasKey('maxReservationLunch', $content);
        $this->assertArrayHasKey('maxReservationDiner', $content);
        $this->assertArrayHasKey('maxSeatLunch', $content);
        $this->assertArrayHasKey('maxSeatDiner', $content);
        $this->assertArrayHasKey('hour', $content);
        $this->assertArrayHasKey('minute', $content);
        $this->assertArrayHasKey('howManyGuest', $content);
        $this->assertArrayHasKey('date', $content);

        $this->assertEquals(4, $content['maxReservationLunch']);
        $this->assertEquals(6, $content['maxSeatDiner']);
    }
}
