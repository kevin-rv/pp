<?php


namespace App\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class AuthenticatedClientRequestWrapper
{
    /**
     * @var KernelBrowser
     */
    private $client;
    /**
     * @var array
     */
    private $tokens;

    private $userKey = 0;

    public function __construct(KernelBrowser $client, array $tokens)
    {
        $this->client = $client;
        $this->tokens = $tokens;
    }

    public function setUser($key): self
    {
        $this->userKey = $key;

        return $this;
    }

    public function request(
        string $method,
        string $uri,
        array $parameters = [],
        array $files = [],
        array $server = [],
        string $content = null,
        bool $changeHistory = true
    ): ?Crawler {
        $server['HTTP_Authorization'] = sprintf('Bearer %s', $this->tokens[$this->userKey]);

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
