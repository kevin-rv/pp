<?php


namespace App\Tests;

class PlanningTest extends AbstractAuthenticatedTest
{   //CREATE
    public function testCreatePlanningIsSuccessful()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'planning123']);

        $this->assertResponseIsSuccessful();
    }

    public function testCreatePlanningWithEmptyNameFail()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '']);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreatePlanningFailIfUserIsNotConnected()
    {
        $this->client->request('POST', '/planning', ['name' => 'planning123']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreatePlanningWithPlanningNameAlreadyExistMustFailed()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'randomName']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'randomName']);
        $this->assertResponseStatusCodeSame(400);
    }
    // GET
    public function testGetOneCreatedPlanningIsSuccessful()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'my-planning']);
        $this->assertResponseStatusCodeSame(200);
        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);

        $this->clientRequestAuthenticated('GET', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(200);
        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals('my-planning', $plannings[0]['name']);
    }

    public function testGetAllCreatedPlanningIsSuccessful()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '1']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '2']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '3']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('GET', '/planning');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneNonexistentPlanningFailed()
    {
        $this->clientRequestAuthenticated('GET', '/planning/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetOneCreatedPlanningFailUserIsNotConnected() // no
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'azerty']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('GET', '/planning/'.$plannings['0']['id']);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllCreatedPlanningFailUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '1']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '2']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '3']);
        $this->assertResponseStatusCodeSame(200);

        $this->client->request('GET', '/planning');
        $this->assertResponseStatusCodeSame(401);
    }

    //  UPDATE
    public function testUpdatePlanningFailIfUserIsNotConnected() //no
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'my-planning']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('PATCH', '/planning/'.$plannings[0]['id'], ['name' => 'plouf']);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateOnePlanning()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'petit']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('PATCH', '/planning/'.$plannings[0]['id'], ['name' => 'Grand']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals('Grand', $plannings[0]['name']);
    }

    public function testUpdatePlanningDoesNotExist()
    {
        $this->clientRequestAuthenticated('PATCH', '/planning/0', ['name' => 'Grand']);

        $this->assertResponseStatusCodeSame(404);
    }

    // DELETE
    public function testDeleteOnePlanning() // vérifier que le planning n'existe plus
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'bye']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('GET', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('DELETE', '/planning/'.$plannings[0]['id'], ['name' => 'bye']);
        $this->assertResponseStatusCodeSame(200);
    }

//    public function testDeleteAllPlanning()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '1']);
//        $this->assertResponseStatusCodeSame(200);
//
//        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '2']);
//        $this->assertResponseStatusCodeSame(200);
//
//        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '3']);
//        $this->assertResponseStatusCodeSame(200);
//
//        $this->clientRequestAuthenticated('DELETE', '/planning');
//
//        $this->assertResponseStatusCodeSame(200);
//    }

    public function testDeletePlanningDoesNotExist() // no
    {
        $this->clientRequestAuthenticated('DELETE', '/planning/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOnePlanningFailIfUserIsNotConnected() // no
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'bye']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('GET', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        $this->client->Request('DELETE', '/planning/'.$plannings[0]['id']);

        $this->assertResponseStatusCodeSame(401);
    }

//    public function testDeleteAllPlanningFailIfUserIsNotConnected()
//    {
//
//    }
}

