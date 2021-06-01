<?php

namespace App\Tests;

class TaskTest extends AbstractAuthenticatedTest
{
    private function randomTaskData(): array
    {
        return [
            'name' => self::$faker->words(5, true),
            'short_description' => self::$faker->text, ];
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
        $this->clientRequestAuthenticated('POST', sprintf('/planning/%s/task', $plannings[0]['id']), $this->randomTaskData());

        $this->assertResponseIsSuccessful();
    }

//
//    public function testCreateTaskPlanningNotExist()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning/0/task', $this->randomTaskData());
//
//        $this->assertResponseStatusCodeSame(404);
//    }
//
//    public function testCreateTaskFailIfUserIsNotPlanning()
//    {
//    }
//
//    public function testCreateTaskFailIfUserIsNotConnected()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $plannings = json_decode($this->client->getResponse()->getContent(), true);
//        $this->client->request('POST', '/planning'.$plannings[0]['id'].'/task', $this->randomTaskData());
//
//        $this->assertResponseStatusCodeSame(401);
//    }
//
//    public function testCreateTaskIfNotMyPlanning()
//    {
//    }
//
//    // GET
//    public function testGetAllTask()
//    {
//    }
//
//    public function testGetOneTask()
//    {
//        $this->clientRequestAuthenticated('POST', '/planning', $this->randomPlanningData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $plannings = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('POST', '/planning'.$plannings[0]['id'].'/task', $this->randomTaskData());
//        $this->assertResponseStatusCodeSame(200);
//
//        $task = json_decode($this->client->getResponse()->getContent(), true);
//        $this->clientRequestAuthenticated('GET', '/planning'.$plannings[0]['id'].$task, $this->randomTaskData());
//
//
//    }
//
//    public function testGetOneTaskDoesNotExist()
//    {
//    }
//
//    public function testGetOneTaskIfPlanningIsNotExit()
//    {
//    }
//
//    public function testGetAllTaskIfPlanningIsNotExit()
//    {
//    }
//
//    public function testGetOneTaskIfUserIsNotConnected()
//    {
//    }
//
//    public function testGetAllTaskIfUserIsNotConnected()
//    {
//    }
//
//    public function testGetTaskIfNotMyPlanning()
//    {
//    }
//
//    // UPDATE
//
//    public function testUpdateOneTask()
//    {
//    }
//
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
