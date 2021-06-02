<?php

namespace App\Tests;

class PlanningTest extends AbstractAuthenticatedTest
{
    private function randomPlanningData(): array
    {
        return ['name' => self::$faker->words(5, true)];
    }

    public function testCreatePlanningIsSuccessful()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());

        $this->assertResponseIsSuccessful();
    }

    public function testCreatePlanningWithEmptyNameFail()
    {
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => '']);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreatePlanningFailIfUserIsNotConnected()
    {
        $this->client->request('POST', '/planning', $this->randomPlanningData());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreatePlanningWithPlanningNameAlreadyExistMustFailed()
    {
        $planningName = self::$faker->words(3, true);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => $planningName]);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', ['name' => $planningName]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetOneCreatedPlanningIsSuccessful()
    {
        $planningName = self::$faker->words(3, true);
        $this->clientRequestAuthenticated('POST', '/planning', ['name' => $planningName]);
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        $this->clientRequestAuthenticated('GET', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals($planningName, $plannings[0]['name']);
    }

    public function testGetAllCreatedPlanningIsSuccessful()
    {
        $planningsName = [
            self::$faker->words(3, true),
            self::$faker->words(3, true),
            self::$faker->words(3, true),
        ];

        foreach ($planningsName as $planningName) {
            $this->clientRequestAuthenticated('POST', '/planning', ['name' => $planningName]);
            $this->assertResponseStatusCodeSame(200);
        }

        $this->clientRequestAuthenticated('GET', '/planning');
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        $planningsNameFromResponse = [];

        foreach ($plannings as $planning) {
            $planningsNameFromResponse[] = $planning['name'];
        }

        foreach ($planningsName as $planningName) {
            self::assertContains($planningName, $planningsNameFromResponse);
        }
    }

    public function testGetOneNonexistentPlanningFailed()
    {
        $this->clientRequestAuthenticated('GET', '/planning/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetOneCreatedPlanningFailUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('GET', '/planning/'.$plannings['0']['id']);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllCreatedPlanningFailUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $this->client->request('GET', '/planning');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdatePlanningFailIfUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('PATCH', '/planning/'.$plannings[0]['id'], $this->randomPlanningData());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateOnePlanning()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $planningName = self::$faker->words(3, true);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('PATCH', '/planning/'.$plannings[0]['id'], ['name' => $planningName]);
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($planningName, $plannings[0]['name']);
    }

    public function testUpdatePlanningDoesNotExist()
    {
        $this->clientRequestAuthenticated('PATCH', '/planning/0', $this->randomPlanningData());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOnePlanning()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('DELETE', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('GET', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeletePlanningDoesNotExist()
    {
        $this->clientRequestAuthenticated('DELETE', '/planning/0');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOnePlanningFailIfUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('DELETE', '/planning/'.$plannings[0]['id']);
        $this->assertResponseStatusCodeSame(401);
    }
}
