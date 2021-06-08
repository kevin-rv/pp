<?php

namespace App\Tests;

class ContactTest extends AbstractAuthenticatedTest
{
    private function randomContactData(): array
    {
        return [
            'name' => self::$faker->name,
            'phoneNumber' => self::$faker->phoneNumber,
            'home' => self::$faker->address,
            'birthday' => self::$faker->date(),
            'email' => self::$faker->email,
            'relationship' => self::$faker->word,
            'work' => self::$faker->jobTitle,

        ];
    }


    // CREATE
    public function testCreateContactIsSuccessful()
    {
        $data =$this->randomContactData();
        $this->authenticatedClient->request(
            'POST',
            $this->urlGenerator->generate('contact_create'),
            $data
        );

        $this->assertResponseIsSuccessful();
        $contact = json_decode($this->client->getResponse()->getContent(), true)[0];

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
        for ($i = 0; $i < 2; $i++) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'contact_create'
                ),
                $this->randomContactData()
            );
            $this->assertResponseStatusCodeSame(200);
            $createdContacts[] = json_decode($this->client->getResponse()->getContent(), true)[0];
        }

        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'contact_list'
            )
        );
        $this->assertResponseStatusCodeSame(200);
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => $contact[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => $contact[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(401);
    }
    public function testGetAllContactFailIfUserIsNotConnected()
    {
        for ($i = 0; $i < 2; $i++) {
            $this->authenticatedClient->request(
                'POST',
                $this->urlGenerator->generate(
                    'contact_create'
                ),
                $this->randomContactData()
            );
        }
        $this->assertResponseStatusCodeSame(200);

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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'GET',
            $this->urlGenerator->generate(
                'contact',
                ['contactId' => $contact[0]['id']]
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $newContactData = $this->randomContactData();
        $newContactData['id'] = $contact[0]['id'];

        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId'  => $contact[0]['id']]
            ),
            $newContactData
        );
        $this->assertResponseStatusCodeSame(200);
        $contact = json_decode($this->client->getResponse()->getContent(), true);
        self::assertEquals($newContactData, $contact[0]);
    }

    public function testUpdateContactFailDoesNotExist()
    {
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId'  => 0]
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $newContactData = $this->randomContactData();
        $newContactData['id'] = $contact[0]['id'];

        $this->client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId'  => $contact[0]['id']]
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'PATCH',
            $this->urlGenerator->generate(
                'contact_update',
                ['contactId'  => $contact[0]['id']]
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => $contact[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(200);
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => $contact[0]['id']]
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
        $this->assertResponseStatusCodeSame(200);

        $contact = json_decode($this->client->getResponse()->getContent(), true);
        $this->authenticatedClient->setUser(1)->request(
            'DELETE',
            $this->urlGenerator->generate(
                'contact_delete',
                ['contactId' => $contact[0]['id']]
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
