<?php

namespace App\Tests;

use App\Entity\User;
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

    public function getRandomUserData(array $forceCustomValues = []): array
    {
        $this->faker = Factory::create('fr_FR');

        $data = [
            'email' => $this->faker->email,
            'password' => 'password',
            'birthday' => $this->faker->date(),
            'home' => $this->faker->address,
            'work' => $this->faker->jobTitle,
            'name' => $this->faker->name,
            'phoneNumber' => $this->faker->phoneNumber,
        ];

        foreach ($forceCustomValues as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }

    public function testCreateWithGoodValuesCreateUser(): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData());

        $this->assertResponseIsSuccessful();
    }

    public function testCreateWithBadBirthdayReturn400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData(['birthday' => 'text']));

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(User::BIRTHDAY_ERROR_MSG, $response['error']);
    }

    public function testCreateWithBadEmailReturn400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData(['email' => 'not an email!']));

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(User::EMAIL_ERROR_MSG, $response['error']);
    }

    public function testCreateWithBadPhoneNumberReturn400(): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData(['phoneNumber' => 'bad phone number']));

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(User::PHONE_NUMBER_ERROR_MSG, $response['error']);
    }

    public function testCreateWithExistingEmailReturn400(): void
    {
        $data = $this->getRandomUserData();

        $client = static::createClient();

        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/user', $this->getRandomUserData(['email' => $data['email']]));
        $this->assertResponseStatusCodeSame(400);
    }

    public function testGetExistingUser()
    {
        $data = $this->getRandomUserData();
        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/user', [], [], ['HTTP_Authorization' => "Bearer $token"]);
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetUserIsNotConnected()
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData());
        $this->assertResponseStatusCodeSame(200);

        $client->request('GET', '/user');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateUser()
    {
        $data = $this->getRandomUserData();
        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $token = json_decode($client->getResponse()->getContent(), true);
        $client->request('PATCH', '/user', $this->getRandomUserData(), [], ['HTTP_Authorization' => "Bearer $token"]);

        $this->assertResponseStatusCodeSame(200);
    }

    public function testUpdateWithBadBirthdayReturn400(): void
    {
        $data = $this->getRandomUserData();
        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $data['birthday'] = 'bonjour';

        $token = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/user', $data, [], ['HTTP_Authorization' => "Bearer $token"]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(User::BIRTHDAY_ERROR_MSG, $response['error']);
    }

    public function testUpdateWithBadEmailReturn400(): void
    {
        $data = $this->getRandomUserData();
        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $token = json_decode($client->getResponse()->getContent(), true);
        $data['email'] = 'bad email';
        $client->request('PATCH', '/user', $data, [], ['HTTP_Authorization' => "Bearer $token"]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(User::EMAIL_ERROR_MSG, $response['error']);
    }

    public function testUpdateWithBadPhoneNumberReturn400(): void
    {
        $data = $this->getRandomUserData();
        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $token = json_decode($client->getResponse()->getContent(), true);
        $data['phoneNumber'] = 'bad phone number';
        $client->request('PATCH', '/user', $data, [], ['HTTP_Authorization' => "Bearer $token"]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals(User::PHONE_NUMBER_ERROR_MSG, $response['error']);
    }

    public function testUpdateWithExistingEmailReturn400(): void
    {
        $data = $this->getRandomUserData();
        $data2 = $this->getRandomUserData();

        $client = static::createClient();

        $client->request('POST', '/user', $data);
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/user', $data2);
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/auth', [
            'email' => $data2['email'],
            'password' => $data2['password'],
        ]);
        $this->assertResponseIsSuccessful();

        $token = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/user', ['email' => $data['email']], [], ['HTTP_Authorization' => "Bearer $token"]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testDeleteUser()
    {
        $data = $this->getRandomUserData();

        $client = static::createClient();

        $client->request('POST', '/user', $data);
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $this->assertResponseIsSuccessful();

        $token = json_decode($client->getResponse()->getContent(), true);

        $client->request('DELETE', '/user', [], [], ['HTTP_Authorization' => "Bearer $token"]);
        $this->assertResponseIsSuccessful();

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function testDeleteUserIsNotConnected()
    {
        $data = $this->getRandomUserData();
        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('DELETE', '/user');

        $this->assertResponseStatusCodeSame(401);
    }
}
