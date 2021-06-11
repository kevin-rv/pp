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
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );

        $this->assertResponseIsSuccessful();
    }

    public function testCreatePlanningWithEmptyNameFail()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('planning_create'),
            ['name' => '']
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
        $planningData = $this->randomPlanningData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $planningData
        );
        $this->assertResponseStatusCodeSame(200);

        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $planningData
        );
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetOneCreatedPlanningIsSuccessful()
    {
        $planningData = $this->randomPlanningData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $planningData
        );
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => $plannings[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);

        self::assertEquals($planningData['name'], $plannings[0]['name']);
    }

    public function testGetAllCreatedPlanningIsSuccessful()
    {
        $createdPlannings = [];
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'planning_create'
                ),
                $this->randomPlanningData()
            );
            $this->assertResponseStatusCodeSame(200);
            $createdPlannings[] = json_decode($this->client->getResponse()->getContent(), true)[0];
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
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => $plannings[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllCreatedPlanningFailUserIsNotConnected()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'planning_create'
                ),
                $this->randomPlanningData()
            );
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
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'planning_update',
                ['planningId' => $plannings[0]['id']]
            ),
            $this->randomPlanningData()
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateOnePlanning()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );
        $this->assertResponseStatusCodeSame(200);

        $planningName = self::$faker->words(3, true);
        $plannings = json_decode($this->client->getResponse()->getContent(), true);

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'planning_update',
                ['planningId' => $plannings[0]['id']]
            ),
            ['name' => $planningName]
        );
        $this->assertResponseStatusCodeSame(200);

        $plannings = \Safe\json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($planningName, $plannings[0]['name']);
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
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'planning_delete',
                ['planningId' => $plannings[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);

        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'planning',
                ['planningId' => $plannings[0]['id']]
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
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'planning_create'
            ),
            $this->randomPlanningData()
        );
        $this->assertResponseStatusCodeSame(200);

        $plannings = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'planning_delete',
                ['planningId' => $plannings[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }
}
