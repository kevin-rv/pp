<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\JWT;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class AccessSubscriber implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;
    /**
     * @var JWT
     */
    private $jwt;
    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(RouterInterface $router, JWT $jwt, UserRepository $userRepository)
    {
        $this->router = $router;
        $this->jwt = $jwt;
        $this->userRepository = $userRepository;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if (!$this->isAuthRequired($event)) {
            return;
        }

        $token = $this->getJWTToken($event);
        $user = $this->getValidUserFromToken($token);

        if (!$user) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized'], 401));
        }

        $event->getRequest()->attributes->set('_user', $user);
    }

    public static function getSubscribedEvents()
    {
        return [
            'kernel.request' => 'onKernelRequest',
        ];
    }

    private function isAuthRequired(RequestEvent $event): bool
    {
        try {
            $authOption = $this->router->getRouteCollection()->get($this->router->match($event->getRequest()->getPathInfo())['_route'])->getOption('auth');

            if (is_bool($authOption)) {
                return $authOption;
            }
        } catch (\Throwable $exception) {
            return true; // If anything goes wrong require authoring
        }

        return true; // Default is require authoring
    }

    private function getJWTToken(RequestEvent $event): ?string
    {
        $authorization = $event->getRequest()->headers->get('Authorization');

        if (!$authorization) {
            return null;
        }

        preg_match('#^Bearer (.+)$#', $authorization, $matches);

        return $matches[1] ?? null;
    }

    private function getValidUserFromToken(?string $token): ?User
    {
        if (!$token) {
            return null;
        }

        try {
            $jwe = $this->jwt->decryptToken($token);
            $payload = json_decode($jwe->getPayload(), true);
            $userId = $payload['user_id'];
            $userEmail = $payload['user_email'];
            $user = $this->userRepository->find($userId);
        } catch (\Throwable $exception) {
            return null;
        }

        if ($user && $user->getEmail() === $userEmail) {
            return $user;
        }

        return null;
    }
}
