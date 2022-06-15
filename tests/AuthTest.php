<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthTest extends WebTestCase
{
    public function testConnectWithGoodCredentials(): void
    {
        $client = static::createClient();
        $client->request('POST', '/auth', [
            'email' => 'email_0@email.com',
            'password' => 'password',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testConnectWithBadCredentials(): void
    {
        $client = static::createClient();
        $client->request('POST', '/auth', [
            'email' => 'bad',
            'password' => 'bad',
        ]);

        $this->assertResponseStatusCodeSame(400);
    }
}
