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
    private $urlGenerator;

    /**
     * @var array
     */
    private $tokens;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create('fr_FR');
//        $clientAuth = $client->request->['HTTP_Authorization'] $this->tokens[$this->userKey];
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
        $BadValue =[
           'email' => 'Bad Email',
           'password' => 'password',
           'birthday' => $this->faker->date(),
           'home' => $this->faker->address,
           'work' => $this->faker->jobTitle,
           'name' => $this->faker->name,
           'phoneNumber' => $this->faker->phoneNumber,
        ] ;

        $client = static::createClient();
        $client->request('POST', '/user', $BadValue);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('email MUST to be a valid email', $response['error']);
    }


    public function testCreateWithBadPhoneNumberReturn400(): void
    {
        $BadValue =[
            'email' => $this->faker->email,
            'password' => 'password',
            'birthday' => $this->faker->date(),
            'home' => $this->faker->address,
            'work' => $this->faker->jobTitle,
            'name' => $this->faker->name,
            'phoneNumber' => 'Bad PhoneNumber',
        ] ;
        $client = static::createClient();
        $client->request('POST', '/user', $BadValue);

        $this->assertResponseStatusCodeSame(400);

        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('phone number MUST match regex format: ^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$', $response['error']);
    }

    public function testCreateWithExistingEmailReturn400(): void
    {
        $value =[
            'email' => '1234@email.com',
            'password' => 'password',
            'birthday' => $this->faker->date(),
            'home' => $this->faker->address,
            'work' => $this->faker->jobTitle,
            'name' => $this->faker->name,
            'phoneNumber' => $this->faker->phoneNumber,
        ] ;
        $client = static::createClient();
        $client->request('POST', '/user', $value);

        $this->assertResponseStatusCodeSame(200);

        $client->request('POST', '/user', $value);

        $this->assertResponseStatusCodeSame(400);
    }


    public function testGetUser()
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData());
        $this->assertResponseStatusCodeSame(200);
        $key = json_decode($this->client->getResponse()->getContent(), true);
        $client->request('GET', '/user', ['HTTP_Authorization'], $this->tokens[$key]);

        $this->assertResponseStatusCodeSame(200);
    }


    public function testGetUserIsNotConnected()
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData());
        $this->assertResponseStatusCodeSame(200);

        $client->request(
            'GET',
            $this->urlGenerator->generate(
                'user_view'
            )
        );
        $this->assertResponseStatusCodeSame(404);
    }


    public function testUpdateUser()
    {
        $client = static::createClient();
        $client->request('POST', '/user', $this->getRandomUserData());
        $this->assertResponseStatusCodeSame(200);

        $client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'user_update'
            ),
            $this->getRandomUserData()
        );
        $this->assertResponseStatusCodeSame(200);
    }


    public function testUpdateWithBadBirthdayReturn400($userData): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $userData);
        $this->assertResponseStatusCodeSame(200);
        $response = json_decode($client->getResponse()->getContent(), true);

        $userData['birthday'] = 'bonjour'   ;
        $client->request(
            'PATCH',
            $this->urlGenerator->generate(
                'user_update'
            ),
            $userData
        );

        $this->assertResponseStatusCodeSame(400);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('birthday MUST to be in format yyyy-mm-dd', $response['error']);
    }


    public function testUpdateWithBadEmailReturn400($userData): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $userData);
        $this->assertResponseStatusCodeSame(200);
        $response = json_decode($client->getResponse()->getContent(), true);

        $userData['email'] = 'bad email dzedzed dezdze';
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'user_update'
            ),
            $userData
        );

        $this->assertResponseStatusCodeSame(400);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('email MUST to be a valid email', $response['error']);
    }


    public function testUpdateWithBadPhoneNumberReturn400($userData): void
    {
        $client = static::createClient();
        $client->request('POST', '/user', $userData);
        $this->assertResponseStatusCodeSame(200);
        $response = json_decode($client->getResponse()->getContent(), true);

        $userData['phoneNumber'] = 'bad phone number';
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'user_update'
            ),
            $userData
        );

        $this->assertResponseStatusCodeSame(400);

        $this->assertArrayHasKey('error', $response);
        $this->assertEquals('phone number MUST match regex format: ^(\+\d{1,4}\s*)?(\(\d{1,5}\))?(\s*\d{1,2}){1,6}$', $response['error']);
    }

    public function testUpdateWithExistingEmailReturn400($userData): void
    {
        $userData['email'] = '123@email.com';
        $client = static::createClient();
        $client->request('POST', '/user', $userData);
        $this->assertResponseStatusCodeSame(200);
        $userData['email'] = '123@email.com';
        $this->authenticatedClient->request(
            'PATCH',
            $this->urlGenerator->generate(
                'user_update'
            ),
            $userData
        );

        $this->assertResponseStatusCodeSame(400);
    }


    public function testDeleteUser($userData)
    {
        $client->request('POST', '/user', $userData);
        $this->assertResponseStatusCodeSame(200);

        $this->authenticatedClient->request(
            'DELETE',
            $this->urlGenerator->generate(
                'user_delete'
            ),
            $userData
        );
        $this->assertResponseStatusCodeSame(200);
    }


    public function testDeleteUserIsNotConnected($userData)
    {
        $client->request('POST', '/user', $userData);
        $this->assertResponseStatusCodeSame(200);

        $this->client->request(
            'DELETE',
            $this->urlGenerator->generate(
                'user_delete'
            ),
            $userData
        );
        $this->assertResponseStatusCodeSame(404);
    }
}
