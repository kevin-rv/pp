<?php

namespace App\Tests;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserTest extends WebTestCase
{
    /**
     * @var Generator
     */
    private $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('fr_FR');
    }

    public function getValidUserData(): array
    {
        $this->faker = Factory::create('fr_FR');

        return [
            [
                [
                    'email' => $this->faker->email,
                    'password' => 'password',
                    'birthday' => $this->faker->date(),
                    'name' => $this->faker->name,
                    'phoneNumber' => $this->faker->phoneNumber,
                ]
            ]
        ];
    }

    /**
     * @dataProvider getValidUserData
     */
    public function testCreateWithGoodValuesCreateUser($userData): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $userData);

        $this->assertResponseIsSuccessful();
    }

    /**
     * @dataProvider getValidUserData
     */
    public function testCreateWithBadBirthdayReturn400($userData): void
    {
        $userData['birthday'] = 'bonjour';
        $client = static::createClient();
        $client->request('POST', '/user', $userData);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('birthday MUST to be in format yyyy-mm-dd', $response['error']);
    }

    /**
     * @dataProvider getValidUserData
     */
    public function testCreateWithBadEmailReturn400($userData): void
    {
        $userData['email'] = 'bad email dzedzed dezdze';
        $client = static::createClient();
        $client->request('POST', '/user', $userData);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('email MUST to be a valid email', $response['error']);
    }

    /**
     * @dataProvider getValidUserData
     */
    public function testCreateWithBadPhoneNumberReturn400($userData): void
    {
        $userData['phoneNumber'] = 'bad phone number';
        $client = static::createClient();
        $client->request('POST', '/user', $userData);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('phone number MUST match regex format: ^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$', $response['error']);
    }


    /**
     * @dataProvider getValidUserData
     */
    public function testCreateWithExistingEmailReturn400($userData): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $userData);

        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/user', $userData);

        $this->assertResponseStatusCodeSame(400);
    }
}
