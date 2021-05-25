<?php


namespace App\Tests;

class PlanningTest extends AbstractAuthenticatedTest
{
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

    public function testGetOneCreatedPlanningIsSuccessful()
    {
//        $this->clientRequestAuthenticated('POST', '/planning', ['name' => 'my-planning']);
//
        // recup de l'id dans la requete

        // $this->clientRequestAuthenticated('GET', '/planning/{id}');

        // $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneNonexistentPlanningFailed()
    {
        $this->clientRequestAuthenticated('GET', '/planning/0');

        $this->assertResponseStatusCodeSame(404);
    }
}
