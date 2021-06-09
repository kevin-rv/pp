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
                $this->markTestIncomplete(sprintf('Fail to instanciate planning for user %s', $k));
            }
            $plannings = json_decode($this->client->getResponse()->getContent(), true);
            self::$userPlanningIds[] = $plannings[0]['id'];
        }
        $this->authenticatedClient->setUser(0);
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
        $data = $this->randomTaskData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('task_create', ['planningId' => self::$userPlanningIds[0]]),
            $data
        );

        $this->assertResponseIsSuccessful();
        $task = json_decode($this->client->getResponse()->getContent(), true)[0];

        self::assertEquals($data['shortDescription'], $task['shortDescription']);
        self::assertEquals($data['done'], $task['done']);
        self::assertEquals($data['doneLimitDate'], $task['doneLimitDate']);
    }

    public function testCreateTaskPlanningNotExist()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => 0]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateTaskFailIfUserIsNotConnected()
    {
        $this->client->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateTaskFailIfNotUserPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[1]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // GET
    public function testGetAllTask()
    {
        $createdTasks = [];
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'task_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
            $this->assertResponseStatusCodeSame(200);
            $createdTasks[] = json_decode($this->client->getResponse()->getContent(), true)[0];
        }

        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => self::$userPlanningIds[0]]
            )
        );
        $this->assertResponseStatusCodeSame(200);
        $allTasks = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($createdTasks as $task) {
            $this->assertContains($task, $allTasks);
        }
    }

    public function testGetOneTask()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneTaskDoesNotExist()
    {
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetOneTaskIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllTaskIfUserIsNotConnected()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'task_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
        }
        $this->assertResponseStatusCodeSame(200);

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => self::$userPlanningIds[0]]
            )
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetOneTaskFailIfNotUserPlanning()
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
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAllTaskFailIfNotUserPlanning()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->setUser(0)->request(
                'POST',
                $this->urlGenerator->generate(
                    'task_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
            $this->assertResponseStatusCodeSame(200);
        }

        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => self::$userPlanningIds[0]]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // UPDATE

    public function testUpdateOneTask()
    {
        $this->authenticatedClient->request(
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

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            ),
            $newTaskData
        );
        $this->assertResponseStatusCodeSame(200);
        $task = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($newTaskData, $task[0]);
    }

    public function testUpdateTaskDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => 0]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateOneTaskFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $value = [
            'id' => $task[0]['id'],
            'shortDescription' => self::$faker->text,
            'done' => self::$faker->date(),
            'doneLimitDate' => self::$faker->date(),
        ];

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            ),
            ['shortDescription' => $value['shortDescription'], 'done' => $value['done'], 'doneLimitDate' => $value['doneLimitDate']]
        );
        $this->assertResponseStatusCodeSame(401);
    }

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
                ['planningId' => self::$userPlanningIds[1], 'taskId' => $task[0]['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // DELETE

    public function testDeleteOneTask()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testDeleteTaskDoesNotExist()
    {
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOneTaskFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

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
                ['planningId' => self::$userPlanningIds[0], 'taskId' => $task[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
