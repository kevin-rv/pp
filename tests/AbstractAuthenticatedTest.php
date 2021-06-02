<?php

namespace App\Tests;

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
    private static $token;

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

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->urlGenerator = $this->client->getContainer()->get('router')->getGenerator();

        if (self::$token) {
            return;
        }

        for ($i = 0; $i < 2; $i++) {

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

            self::$token[$i] = json_decode($this->client->getResponse()->getContent(), true);
        }
    }

    public function clientRequestAuthenticated(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true,
        int $tokenId = 0
    ): ?Crawler {
        $server['HTTP_Authorization'] = 'Bearer '.self::$token[$tokenId];

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
