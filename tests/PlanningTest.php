<?php

namespace App\Tests;

class PlanningTest extends AbstractAuthenticatedTest
{
    private function randomPlanningData(array $customValues = []): array
    {
        $data = ['name' => $this->faker->words(5, true)];

        foreach ($customValues as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    public function testCreatePlanningIsSuccessful(): array
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );

        $this->assertResponseIsSuccessful();

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    public function testCreatePlanningWithEmptyNameFail()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('planning_create'),
            $this->randomPlanningData(['name' => ''])
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreatePlanningFailIfUserIsNotConnected()
    {
        $this->client->request(
            'POST',
            $this->urlGenerator->generate('planning_create'),
            $this->randomPlanningData()
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreatePlanningWithPlanningNameAlreadyExistMustFailed()
    {
        $planning = $this->testCreatePlanningIsSuccessful();

        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $planning
        );

        $this->assertResponseStatusCodeSame(409);
    }

    public function testGetOneCreatedPlanningIsSuccessful()
    {
        $planningData = $this->testCreatePlanningIsSuccessful();

        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => $planningData['id']]
            )
        );

        $this->assertResponseStatusCodeSame(200);

        $planning = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals($planningData['name'], $planning['name']);
    }

    public function testGetAllCreatedPlanningIsSuccessful()
    {
        $createdPlannings = [];
        for ($i = 0; $i < 2; ++$i) {
            $createdPlannings[] = $this->testCreatePlanningIsSuccessful();
        }

        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'planning_list'
            )
        );

        $this->assertResponseStatusCodeSame(200);

        $allPlannings = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($createdPlannings as $planning) {
            self::assertContains($planning, $allPlannings);
        }
    }

    public function testGetOneNonexistentPlanningFailed()
    {
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => 0]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetOneCreatedPlanningFailUserIsNotConnected()
    {
        $planningData = $this->testCreatePlanningIsSuccessful();

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => $planningData['id']]
            )
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllCreatedPlanningFailUserIsNotConnected()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->testCreatePlanningIsSuccessful();
        }

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'planning_list'
            )
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdatePlanningFailIfUserIsNotConnected()
    {
        $planningData = $this->testCreatePlanningIsSuccessful();

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'planning_update',
                ['planningId' => $planningData['id']]
            ),
            $this->randomPlanningData()
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateOnePlanning()
    {
        $planningData = $this->testCreatePlanningIsSuccessful();
        $randomPlanningData = $this->randomPlanningData();

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'planning_update',
                ['planningId' => $planningData['id']]
            ),
            $randomPlanningData
        );
        $this->assertResponseStatusCodeSame(200);

        $updatedPlanningData = json_decode($this->client->getResponse()->getContent(), true);
        $randomPlanningData['id'] = $updatedPlanningData['id'];

        self::assertEquals($randomPlanningData, $updatedPlanningData);
    }

    public function testUpdatePlanningDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'planning_update',
                ['planningId' => 0]
            ),
            $this->randomPlanningData()
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOnePlanning()
    {
        $planning = $this->testCreatePlanningIsSuccessful();

        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'planning_delete',
                ['planningId' => $planning['id']]
            )
        );

        $this->assertResponseStatusCodeSame(200);

        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => $planning['id']]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeletePlanningDoesNotExist()
    {
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'planning_delete',
                ['planningId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOnePlanningFailIfUserIsNotConnected()
    {
        $planning = $this->testCreatePlanningIsSuccessful();

        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'planning_delete',
                ['planningId' => $planning['id']]
            )
        );

        $this->assertResponseStatusCodeSame(401);
    }
}
