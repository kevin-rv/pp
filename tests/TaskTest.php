<?php

namespace App\Tests;

class TaskTest extends AbstractPlanningRequiredTest
{
    private function randomTaskData(): array
    {
        return [
            'shortDescription' => $this->faker->text,
            'done' => $this->faker->date(),
            'doneLimitDate' => $this->faker->date(),
        ];
    }

    public function testCreateTaskIsSuccessful()
    {
        $data = $this->randomTaskData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('task_create', ['planningId' => $this->userPlanningIds[0]]),
            $data
        );

        $this->assertResponseIsSuccessful();
        $task = json_decode($this->client->getResponse()->getContent(), true);
        $data['id'] = $task['id'];

        self::assertEquals($data, $task);
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
                ['planningId' => $this->userPlanningIds[0]]
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
                ['planningId' => $this->userPlanningIds[1]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAllTask()
    {
        $createdTasks = [];
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'task_create',
                    ['planningId' => $this->userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
            $this->assertResponseStatusCodeSame(200);
            $createdTasks[] = json_decode($this->client->getResponse()->getContent(), true);
        }

        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => $this->userPlanningIds[0]]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
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
                ['planningId' => $this->userPlanningIds[0], 'taskId' => 0]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
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
                    ['planningId' => $this->userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
        }
        $this->assertResponseStatusCodeSame(200);

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => $this->userPlanningIds[0]]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'task',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
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
                    ['planningId' => $this->userPlanningIds[0]]
                ),
                $this->randomTaskData()
            );
            $this->assertResponseStatusCodeSame(200);
        }

        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'task_list',
                ['planningId' => $this->userPlanningIds[0]]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateOneTask()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $newTaskData = $this->randomTaskData();
        $newTaskData['id'] = $task['id'];

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
            ),
            $newTaskData
        );
        $this->assertResponseStatusCodeSame(200);
        $task = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($newTaskData, $task);
    }

    public function testUpdateTaskDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => 0]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $value = [
            'id' => $task['id'],
            'shortDescription' => $this->faker->text,
            'done' => $this->faker->date(),
            'doneLimitDate' => $this->faker->date(),
        ];

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(0)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'task_update',
                ['planningId' => $this->userPlanningIds[1], 'taskId' => $task['id']]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOneTask()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'task_create',
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
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
                ['planningId' => $this->userPlanningIds[0], 'taskId' => 0]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomTaskData()
        );
        $this->assertResponseStatusCodeSame(200);

        $task = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'task_delete',
                ['planningId' => $this->userPlanningIds[0], 'taskId' => $task['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
