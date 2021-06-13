<?php

namespace App\Tests;

abstract class AbstractPlanningRequiredTest extends AbstractAuthenticatedTest
{
    /**
     * @var int[]
     */
    protected $userPlanningIds = [];

    public function setUp(): void
    {
        parent::setUp();

        foreach ($this->tokens as $k => $token) {
            $this->authenticatedClient->setUser($k)->request(
                'POST',
                $this->urlGenerator->generate(
                    'planning_create'
                ),
                $this->randomPlanningData()
            );

            if (!$this->client->getResponse()->isSuccessful()) {
                $this->markTestIncomplete(sprintf('Fail to instantiate planning for user %s', $k));
            }

            $planning = json_decode($this->client->getResponse()->getContent(), true);

            $this->userPlanningIds[] = $planning['id'];
        }

        $this->authenticatedClient->setUser(0);
    }

    private function randomPlanningData(): array
    {
        return ['name' => $this->faker->words(5, true)];
    }
}
