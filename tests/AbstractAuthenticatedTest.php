<?php

namespace App\Tests;

use App\Tests\Helper\AuthenticatedClientRequestWrapper;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractAuthenticatedTest extends WebTestCase
{
    /**
     * @var string[]
     */
    protected $tokens = [];

    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @var KernelBrowser
     */
    protected $client;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @var AuthenticatedClientRequestWrapper
     */
    protected $authenticatedClient;

    public function setUp(): void
    {
        $this->faker = Factory::create('fr_FR');
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router')->getGenerator();

        for ($i = 0; count($this->tokens) < 2; ++$i) {
            $email = $this->faker->email;
            $password = 'password';

            $this->client->request('POST', '/user', [
                'email' => $email,
                'password' => $password,
                'birthday' => $this->faker->date(),
                'name' => $this->faker->name,
                'phoneNumber' => $this->faker->phoneNumber,
            ]);

            $this->client->request('POST', '/auth', [
                'email' => $email,
                'password' => $password,
            ]);

            $this->tokens[$i] = json_decode($this->client->getResponse()->getContent(), true);
        }

        $this->authenticatedClient = new AuthenticatedClientRequestWrapper($this->client, $this->tokens);
    }
}
