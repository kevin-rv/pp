<?php

namespace App\Tests;

class TaskTest extends AbstractAuthenticatedTest
{
    private function randomTaskData(): array
    {
        return [
            'shortDescription' => self::$faker->text,
            'done' => self::$faker->date(),
            'doneLimitDate' => self::$faker->date(),
        ];
    }

    private function randomPlanningData(): array
    {
        return ['name' => self::$faker->words(5, true)];
    }

    // CREATE
    public function testCreateTaskIsSuccessful()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);


        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated(
            'POST',
            $this->urlGenerator->generate('task_create', ['planningId' => $plannings[0]['id']]),
            $this->randomTaskData()
        );

        $this->assertResponseIsSuccessful();
    }


    public function testCreateTaskPlanningNotExist()
    {
        $this->clientRequestAuthenticated('POST', '/planning/0/task', $this->randomTaskData());

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateTaskFailIfUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('POST', '/planning/'.$plannings[0]['id'].'/task', $this->randomTaskData());

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateTaskFailIfNotUserPlanning()
    {
        // authentification premier user
        // user qui créer un planning
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        // authentification 2 eme user
        // Créer une tache sur le planning de l'user 1
        $this->clientRequestAuthenticated('POST', '/planning/'.$plannings[0]['id'].'/task', $this->randomTaskData(), [], [], null, true, 1);

        $this->assertResponseStatusCodeSame(404);
    }

    // GET
    public function testGetAllTask()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        for ($i = 0; $i < 2; $i++) {
            $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
            $this->assertResponseStatusCodeSame(200);
        }

        $this->clientRequestAuthenticated('GET', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneTask()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('GET', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());

        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneTaskDoesNotExist()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        $this->clientRequestAuthenticated('GET', sprintf('/planning/%s/task/0', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(404);
    }


    public function testGetOneTaskIfUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request('GET', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllTaskIfUserIsNotConnected()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(200);

        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(200);


        $this->client->request('GET', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());

        $this->assertResponseStatusCodeSame(401);
    }

//    public function testGetTaskIfNotMyPlanning()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $plannings = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $task = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('GET', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());
//    }

    // UPDATE

    public function testUpdateOneTask()
    {
        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
        $this->assertResponseStatusCodeSame(200);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->clientRequestAuthenticated('GET', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());




        $task = json_decode($this->client->getResponse()->getContent(), true);
        $value = [
            'id' => $task[0]['id'],
            'shortDescription' => self::$faker->text,
            'done' => self::$faker->date(),
            'doneLimitDate' => self::$faker->date(),
        ];
        $this->clientRequestAuthenticated(
            'PATCH',
            sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']),
            ['shortDescription' => $value['shortDescription'], 'done' => $value['done'], 'doneLimitDate' => $value['doneLimitDate']]
        );
        $this->assertResponseStatusCodeSame(200);
        $task = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($value, $task[0]);
    }

//    public function testUpdateTaskDoesNotExist()
//    {
//    }
//
//    public function testUpdateTaskIfNotMyPlanning()
//    {
//    }
//
//    // DELETE
//
//    public function testDeleteOneTask()
//    {
//    }
//
//    public function testDeleteTaskDoesNotExist()
//    {
//    }
//
//    public function testDeleteOneTaskFailIfUserIsNotConnected()
//    {
//    }
//
//    public function testDeleteTaskIfNotMyPlanning()
//    {
//    }
}
