<?php

namespace App\Tests;

class EventTest extends AbstractAuthenticatedTest
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

    private function randomEventData(): array
    {
        return [
            'shortDescription' => self::$faker->words(3, true),
            'fullDescription' => self::$faker->text,
            'startDatetime' => self::$faker->dateTime->format(DATE_ATOM),
            'endDatetime' => self::$faker->dateTime->format(DATE_ATOM),
            'contacts' => [],
        ];
    }

    private function randomPlanningData(): array
    {
        return ['name' => self::$faker->words(5, true)];
    }

    public function testCreateEventIsSuccessful()
    {
        $data =$this->randomEventData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('event_create', ['planningId' => self::$userPlanningIds[0]]),
            $data
        );

        $this->assertResponseIsSuccessful();
        $event = json_decode($this->client->getResponse()->getContent(), true)[0];

        self::assertEquals($data['shortDescription'], $event['shortDescription']);
        self::assertEquals($data['fullDescription'], $event['fullDescription']);
        self::assertEquals($data['startDatetime'], $event['startDatetime']);
        self::assertEquals($data['endDatetime'], $event['endDatetime']);
    }

    public function testCreateEventPlanningNotExist()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => 0]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testCreateEventFailIfUserIsNotConnected()
    {
        $this->client->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(401);
    }


    public function testCreateEventFailIfNotUserPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[1]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // GET
    public function testGetAllEvent()
    {
        $createdEvents = [];
        for ($i = 0; $i < 2; $i++) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'event_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomEventData()
            );
            $this->assertResponseStatusCodeSame(200);
            $createdEvents[] = json_decode($this->client->getResponse()->getContent(), true)[0];
        }

        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'event_list',
                ['planningId' => self::$userPlanningIds[0]]
            )
        );
        $this->assertResponseStatusCodeSame(200);
        $allEvents = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($createdEvents as $event) {
            $this->assertContains($event, $allEvents);
        }
    }

    public function testGetOneEvent()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => self::$userPlanningIds[0], 'eventId' => $event[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetOneEventDoesNotExist()
    {
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => self::$userPlanningIds[0],'eventId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetOneEventIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => self::$userPlanningIds[0], 'eventId' => $event[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllEventIfUserIsNotConnected()
    {
        for ($i = 0; $i < 2; $i++) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'event_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomEventData()
            );
        }
        $this->assertResponseStatusCodeSame(200);

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'event_list',
                ['planningId' => self::$userPlanningIds[0]]
            )
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetOneEventFailIfNotUserPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => self::$userPlanningIds[0],'eventId' => $event[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAllEventFailIfNotUserPlanning()
    {
        for ($i = 0; $i < 2; $i++) {
            $this->authenticatedClient->setUser(0)->request(
                'POST',
                $this->urlGenerator->generate(
                    'event_create',
                    ['planningId' => self::$userPlanningIds[0]]
                ),
                $this->randomEventData()
            );
            $this->assertResponseStatusCodeSame(200);
        }

        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'event_list',
                ['planningId' => self::$userPlanningIds[0]]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // UPDATE

    public function testUpdateOneEvent()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $newEventData = $this->randomEventData();
        $newEventData['id'] = $event[0]['id'];

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => self::$userPlanningIds[0], 'eventId'  => $event[0]['id']]
            ),
            $newEventData
        );
        $this->assertResponseStatusCodeSame(200);
        $event = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($newEventData, $event[0]);
    }

    public function testUpdateEventDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => self::$userPlanningIds[0], 'eventId'  => 0]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateOneEventFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $newEventData = $this->randomEventData();
        $newEventData['id'] = $event[0]['id'];

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => self::$userPlanningIds[0], 'eventId'  => $event[0]['id']]
            ),
            $newEventData
        );
    }

    public function testUpdateEventIfNotMyPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(0)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => self::$userPlanningIds[1], 'eventId'  => $event[0]['id']]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // DELETE

    public function testDeleteOneEvent()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => self::$userPlanningIds[0], 'eventId' => $event[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);
    }

    public function testDeleteEventDoesNotExist()
    {
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => self::$userPlanningIds[0], 'eventId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOneEventFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => self::$userPlanningIds[0], 'eventId' => $event[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteEventIfNotMyPlanning()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => self::$userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => self::$userPlanningIds[0],'eventId' => $event[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
