<?php

namespace App\Tests;

class TaskTest extends AbstractAuthenticatedTest
{
    /**
     * @var int[]
     */
    private static $userPlanningIds = [];

    public function setUp(): void
    {
        parent::setUp();

        foreach (self::$tokens as $k => $token) {
            $this->authenticatedClient->setUser($k)->request('POST', '/planning', $this->randomPlanningData());
            if (!$this->client->getResponse()->isSuccessful()) {
                $this->markTestIncomplete();
            }
            $plannings = json_decode($this->client->getResponse()->getContent(), true);
            self::$userPlanningIds[] = $plannings[0]['id'];
        }
    }

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
        $data =$this->randomTaskData();
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate('task_create', ['planningId' => self::$userPlanningIds[0]]),
            $data
        );

        $this->assertResponseIsSuccessful();
        $task = json_decode($this->client->getResponse()->getContent(), true)[0];

        self::assertEquals($data['shortDescription'], $task['shortDescription']);
        self::assertEquals($data['done'], $task['done']);
        self::assertEquals($data['doneLimitDate'], $task['doneLimitDate']);
        // Vérifier que la donnée de la tache crée correspond bien au donnée envoyées
    }


    public function testCreateTaskPlanningNotExist() // a revoir
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => 0]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

//    public function testCreateTaskFailIfUserIsNotConnected()
//    {
//
//      //  $this->client->request('POST', '/planning/'.self::$userPlanningIds[0].'/task', $this->randomTaskData());
//        $this->authenticatedClient->setUser(0)->request(
//            'POST',
//            $this->urlGenerator->generate(
//                'task_create',
//                ['planningId' => self::$userPlanningIds[0]]
//            ),
//            $this->randomTaskData()
//        );
//        $this->assertResponseStatusCodeSame(401);
//    }

    public function testCreateTaskFailIfNotUserPlanning()
    {
        $this->authenticatedClient->setUser(0)->request('POST', '/planning/'.self::$userPlanningIds[1].'/task');
        $this->assertResponseStatusCodeSame(404);
    }

    // GET
    public function testGetAllTask()
    {
        for ($i = 0; $i < 2; $i++) {
            $this->authenticatedClient->setUser(0)->request(
                'POST',
                $this->urlGenerator->generate(
                    'task_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
        }
        $this->assertResponseStatusCodeSame(200);

        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        // Vérifier que les taches crées sont bien présentes
    }

    public function testGetOneTask()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneTaskDoesNotExist()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => self::$userPlanningIds[0],'taskId' => 0]
            ),
            $this->randomTaskData()
        );

        $this->assertResponseStatusCodeSame(404);
    }


//    public function testGetOneTaskIfUserIsNotConnected()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $plannings = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $task = json_decode($this->client->getResponse()->getContent(), true);
//        $this->client->request('GET', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(401);
//    }
//
    ////    public function testGetAllTaskIfUserIsNotConnected()
    ////    {
    ////        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
    ////        $this->assertResponseStatusCodeSame(200);
    ////
    ////        $plannings = json_decode($this->client->getResponse()->getContent(), true);
    ////        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
    ////        $this->assertResponseStatusCodeSame(200);
    ////
    ////        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
    ////        $this->assertResponseStatusCodeSame(200);
    ////
    ////        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
    ////        $this->assertResponseStatusCodeSame(200);
    ////
    ////
    ////        $this->client->request('GET', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
    ////
    ////        $this->assertResponseStatusCodeSame(401);
    ////    }
//
    public function testGetTaskFailIfNotUserPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => self::$userPlanningIds[0],'taskId' => $task[0]['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }


//    // UPDATE

    public function testUpdateOneTask()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $newTaskData = $this->randomTaskData();
        $newTaskData['id'] = $task[0]['id'];
        
        $this->authenticatedClient->setUser(0)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => self::$userPlanningIds[0], 'taskId'  => $task[0]['id']]
            ),
            $newTaskData
        );
        $this->assertResponseStatusCodeSame(200);
        $task = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($value, $task[0]);
    }

    public function testUpdateTaskDoesNotExist()
    {
        $this->authenticatedClient->setUser(0)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => self::$userPlanningIds[0], 'taskId'  => 0]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

//    public function testUpdateOneTaskFailIfUserIsNotConnected()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
//        $this->assertResponseStatusCodeSame(200);
//        $plannings = json_decode($this->client->getResponse()->getContent(), true);
//
//        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $task = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('GET', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());
//
//
//        $task = json_decode($this->client->getResponse()->getContent(), true);
//        $value = [
//            'id' => $task[0]['id'],
//            'shortDescription' => self::$faker->text,
//            'done' => self::$faker->date(),
//            'doneLimitDate' => self::$faker->date(),
//        ];
//        $this->client->request(
//            'PATCH',
//            sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']),
//            ['shortDescription' => $value['shortDescription'], 'done' => $value['done'], 'doneLimitDate' => $value['doneLimitDate']]
//        );
//        $this->assertResponseStatusCodeSame(401);
//    }
//
    public function testUpdateTaskIfNotMyPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(0)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => self::$userPlanningIds[1], 'taskId'  => $task[0]['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // DELETE

    public function testDeleteOneTask()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(0)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testDeleteTaskDoesNotExist()
    {
        $this->authenticatedClient->setUser(0)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => 0]
            ),
            $this->randomTaskData()
        );

        $this->assertResponseStatusCodeSame(404);
    }

//    public function testDeleteOneTaskFailIfUserIsNotConnected()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $plannings = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $task = json_decode($this->client->getResponse()->getContent(), true);
//        $this->client->request('DELETE', sprintf('/planning/%s/task/%s', $plannings[0]['id'], $task[0]['id']), $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(401);
//    }
//
    public function testDeleteTaskIfNotMyPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => self::$userPlanningIds[0],'taskId' => $task[0]['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
