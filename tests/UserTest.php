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

    public function getRandomUserData(): array
    {
        $this->faker = Factory::create('fr_FR');

        return [
            'email' => $this->faker->email,
            'password' => 'password',
            'birthday' => $this->faker->date(),
            'home' => $this->faker->address,
            'work' => $this->faker->jobTitle,
            'name' => $this->faker->name,
            'phoneNumber' => $this->faker->phoneNumber,
        ];
    }

    public function testCreateWithGoodValuesCreateUser(): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData());

        $this->assertResponseIsSuccessful();
    }

    public function testCreateWithBadBirthdayReturn400(): void
    {
        $BadValue = [
            'email' => $this->faker->email,
            'password' => 'password',
            'birthday' => 'bonjour',
            'home' => $this->faker->address,
            'work' => $this->faker->jobTitle,
            'name' => $this->faker->name,
            'phoneNumber' => $this->faker->phoneNumber,
        ];

        $client = static::createClient();
        $client->request('POST', '/user', $BadValue);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('birthday MUST to be in format yyyy-mm-dd', $response['error']);
    }

    public function testCreateWithBadEmailReturn400(): void
    {
        $BadValue = [
           'email' => 'Bad Email',
           'password' => 'password',
           'birthday' => $this->faker->date(),
           'home' => $this->faker->address,
           'work' => $this->faker->jobTitle,
           'name' => $this->faker->name,
           'phoneNumber' => $this->faker->phoneNumber,
        ];

        $client = static::createClient();
        $client->request('POST', '/user', $BadValue);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('email MUST to be a valid email', $response['error']);
    }

    public function testCreateWithBadPhoneNumberReturn400(): void
    {
        $BadValue = [
            'email' => $this->faker->email,
            'password' => 'password',
            'birthday' => $this->faker->date(),
            'home' => $this->faker->address,
            'work' => $this->faker->jobTitle,
            'name' => $this->faker->name,
            'phoneNumber' => 'Bad PhoneNumber',
        ];
        $client = static::createClient();
        $client->request('POST', '/user', $BadValue);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('phone number MUST match regex format: ^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$', $response['error']);
    }

    public function testCreateWithExistingEmailReturn400(): void
    {
        $data = $this->getRandomUserData();
        $data2 = $this->getRandomUserData();
        $data2['email'] = $data['email'];

        $client = static::createClient();

        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/user', $data2);
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
        $this->assertEquals('birthday MUST to be in format yyyy-mm-dd', $response['error']);
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
        $data['email'] = 'bad email dzedzed dezdze';
        $client->request('PATCH', '/user', $data, [], ['HTTP_Authorization' => "Bearer $token"]);

        $this->assertResponseStatusCodeSame(400);
        $response = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('email MUST to be a valid email', $response['error']);
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
        $this->assertEquals('phone number MUST match regex format: ^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$', $response['error']);
    }

    public function testUpdateWithExistingEmailReturn400(): void
    {
        $data = $this->getRandomUserData();
        $data2 = $this->getRandomUserData();
        $data3 = $this->getRandomUserData();
        $data2['email'] = $data['email'];

        $client = static::createClient();
        $client->request('POST', '/user', $data);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $token1 = json_decode($client->getResponse()->getContent(), true);

        $client->request('GET', '/user', [], [], ['HTTP_Authorization' => "Bearer $token1"]);

        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/user', $data3);
        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/auth', [
            'email' => $data3['email'],
            'password' => $data3['password'],
        ]);
        $token2 = json_decode($client->getResponse()->getContent(), true);

        $client->request('PATCH', '/user', $data2, [], ['HTTP_Authorization' => "Bearer $token2"]);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testDeleteUser()
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
        $client->request('DELETE', '/user', [], [], ['HTTP_Authorization' => "Bearer $token"]);

        $this->assertResponseStatusCodeSame(200);
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
