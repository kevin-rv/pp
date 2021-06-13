<?php

namespace App\Tests;

class EventTest extends AbstractPlanningRequiredTest
{
    private function randomEventData(): array
    {
        return [
            'shortDescription' => $this->faker->words(3, true),
            'fullDescription' => $this->faker->text,
            'startDatetime' => $this->faker->dateTime->format(DATE_ATOM),
            'endDatetime' => $this->faker->dateTime->format(DATE_ATOM),
            'contacts' => [],
        ];
    }

    public function testCreateEventIsSuccessful()
    {
        $data = $this->randomEventData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('event_create', ['planningId' => $this->userPlanningIds[0]]),
            $data
        );

        $this->assertResponseIsSuccessful();

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $data['id'] = $event['id'];

        self::assertEquals($data, $event);
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
                ['planningId' => $this->userPlanningIds[0]]
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
                ['planningId' => $this->userPlanningIds[1]]
            ),
            $this->randomEventData()
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAllEvent()
    {
        $createdEvents = [];
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'event_create',
                    ['planningId' => $this->userPlanningIds[0]]
                ),
                $this->randomEventData()
            );
            $this->assertResponseStatusCodeSame(200);
            $createdEvents[] = json_decode($this->client->getResponse()->getContent(), true);
        }

        $this->authenticatedClient->setUser(0)->request(
            'GET',
            $this->urlGenerator->generate(
                'event_list',
                ['planningId' => $this->userPlanningIds[0]]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
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
                ['planningId' => $this->userPlanningIds[0], 'eventId' => 0]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllEventIfUserIsNotConnected()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'event_create',
                    ['planningId' => $this->userPlanningIds[0]]
                ),
                $this->randomEventData()
            );
        }
        $this->assertResponseStatusCodeSame(200);

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'event_list',
                ['planningId' => $this->userPlanningIds[0]]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'event',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetAllEventFailIfNotUserPlanning()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->setUser(0)->request(
                'POST',
                $this->urlGenerator->generate(
                    'event_create',
                    ['planningId' => $this->userPlanningIds[0]]
                ),
                $this->randomEventData()
            );
            $this->assertResponseStatusCodeSame(200);
        }

        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'event_list',
                ['planningId' => $this->userPlanningIds[0]]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateOneEvent()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $newEventData = $this->randomEventData();
        $newEventData['id'] = $event['id'];

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
            ),
            $newEventData
        );
        $this->assertResponseStatusCodeSame(200);
        $event = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($newEventData, $event);
    }

    public function testUpdateEventDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => 0]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $newEventData = $this->randomEventData();
        $newEventData['id'] = $event['id'];

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(0)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'event_update',
                ['planningId' => $this->userPlanningIds[1], 'eventId' => $event['id']]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOneEvent()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'event_create',
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
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
                ['planningId' => $this->userPlanningIds[0], 'eventId' => 0]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
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
                ['planningId' => $this->userPlanningIds[0]]
            ),
            $this->randomEventData()
        );
        $this->assertResponseStatusCodeSame(200);

        $event = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'event_delete',
                ['planningId' => $this->userPlanningIds[0], 'eventId' => $event['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
