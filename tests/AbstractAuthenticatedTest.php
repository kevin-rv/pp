<?php


namespace App\Tests;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class AbstractAuthenticatedTest extends WebTestCase
{
    /**
     * @var string
     */
    private static $token;

    /**
     * @var Generator
     */
    private static $faker;
    /**
     * @var KernelBrowser
     */
    protected $client;

    public function setUp(): void
    {
        $this->client = static::createClient();

        if (self::$token) {
            return;
        }

        self::$faker = Factory::create('fr_FR');

        $email =  self::$faker->email;
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

        self::$token = json_decode($this->client->getResponse()->getContent(), true);
    }

    public function clientRequestAuthenticated(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): ?Crawler {
        $server['HTTP_Authorization'] = 'Bearer '.self::$token;

        return $this->client->request(
            $method,
            $uri,
            $parameters,
            $files,
            $server,
            $content,
            $changeHistory
        );
    }
}
