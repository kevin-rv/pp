<?php

namespace App\Tests;

use App\Tests\Helper\AuthenticatedClientRequestWrapper;
use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractAuthenticatedTest extends WebTestCase
{
    /**
     * @var array
     */
    protected static $tokens;

    /**
     * @var Generator
     */
    protected static $faker;

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
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router')->getGenerator();

        for ($i = 0; count(self::$tokens) < 2; $i++) {

            self::$faker = Factory::create('fr_FR');

            $email = self::$faker->email;
            $password = 'password';

            $this->client->request('POST', '/user', [
                'email' => $email,
                'password' => $password,
                'birthday' => self::$faker->date(),
                'name' => self::$faker->name,
                'phoneNumber' => self::$faker->phoneNumber,
            ]);

            $this->client->request('POST', '/auth', [
                'email' => $email,
                'password' => $password,
            ]);

            self::$tokens[$i] = json_decode($this->client->getResponse()->getContent(), true);
        }

        $this->authenticatedClient = new AuthenticatedClientRequestWrapper($this->client, self::$tokens);
    }
}
