<?php

namespace App\Tests;

class ContactTest extends AbstractAuthenticatedTest
{
    private function randomContactData(): array
    {
        return [
            'name' => $this->faker->name,
            'phoneNumber' => $this->faker->phoneNumber,
            'home' => $this->faker->address,
            'birthday' => $this->faker->date(),
            'email' => $this->faker->email,
            'relationship' => $this->faker->word,
            'work' => $this->faker->jobTitle,
        ];
    }

    // CREATE
    public function testCreateContactIsSuccessful()
    {
        $data = $this->randomContactData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('contact_create'),
            $data
        );

        $this->assertResponseIsSuccessful();
        $contact = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals($data['name'], $contact['name']);
        self::assertEquals($data['phoneNumber'], $contact['phoneNumber']);
        self::assertEquals($data['home'], $contact['home']);
        self::assertEquals($data['birthday'], $contact['birthday']);
        self::assertEquals($data['email'], $contact['email']);
        self::assertEquals($data['relationship'], $contact['relationship']);
        self::assertEquals($data['work'], $contact['work']);
    }

    public function testCreateContactWithEmptyNameFail()
    {
        $data = $this->randomContactData();
        $data['name'] = '';

        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('contact_create'),
            $data
        );

        $this->assertResponseStatusCodeSame(400);
    }

    public function testCreateContactFailIfUserIsNotConnected()
    {
        $this->client->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseStatusCodeSame(401);
    }

    // GET
    public function testGetAllContact()
    {
        $createdContacts = [];
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'contact_create'
                ),
                $this->randomContactData()
            );
            $this->assertResponseIsSuccessful();
            $createdContacts[] = json_decode($this->client->getResponse()->getContent(), true);
        }

        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'contact_list'
            )
        );
        $this->assertResponseIsSuccessful();
        $allContacts = json_decode($this->client->getResponse()->getContent(), true);

        foreach ($createdContacts as $contact) {
            $this->assertContains($contact, $allContacts);
        }
    }

    public function testGetOneContact()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => $contact['id']]
            )
        );
        $this->assertResponseIsSuccessful();
    }

    public function testGetOneContactFailDoesNotExist()
    {
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetOneContactFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => $contact['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAllContactFailIfUserIsNotConnected()
    {
        for ($i = 0; $i < 2; ++$i) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'contact_create'
                ),
                $this->randomContactData()
            );
            $this->assertResponseIsSuccessful();
        }

        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'contact_list'
            )
        );

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetOneContactFailIfNotMyUserAccount()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => $contact['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // update

    public function testUpdateOneContact()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $newContactData = $this->randomContactData();
        $newContactData['id'] = $contact['id'];

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId' => $contact['id']]
            ),
            $newContactData
        );
        $this->assertResponseIsSuccessful();
        $contact = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals($newContactData, $contact);
    }

    public function testUpdateContactFailDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId' => 0]
            ),
            $this->randomContactData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateOneContactFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $newContactData = $this->randomContactData();
        $newContactData['id'] = $contact['id'];

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId' => $contact['id']]
            ),
            $newContactData
        );
    }

    public function testUpdateContactFailIfNotMyUserAccount()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId' => $contact['id']]
            ),
            $this->randomContactData()
        );
        $this->assertResponseStatusCodeSame(404);
    }

    // delete

    public function testDeleteOneContact()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => $contact['id']]
            )
        );
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteContactDoesNotExist()
    {
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => 0]
            )
        );

        $this->assertResponseStatusCodeSame(404);
    }

    public function testDeleteOneContactFailIfUserIsNotConnected()
    {
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => $contact['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }

    public function testDeleteContactFailIfNotMyUserAccount()
    {
        $this->authenticatedClient->setUser(0)->request(
            'POST',
            $this->urlGenerator->generate(
                'contact_create'
            ),
            $this->randomContactData()
        );
        $this->assertResponseIsSuccessful();

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => $contact['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
