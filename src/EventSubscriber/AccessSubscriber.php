<?php

namespace App\EventSubscriber;

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
        try {
            $authRequired = true;
            $authOption = $this->router->getRouteCollection()->get($this->router->match($event->getRequest()->getPathInfo())['_route'])->getOption('auth');

            if (is_bool($authOption)) {
                $authRequired = $authOption;
            }
        } catch (\Throwable $exception) {
            $authRequired = true;
        }

        if (!$authRequired) {
            return;
        }

        $authorization = $event->getRequest()->headers->get('Authorization');

        if (!$authorization) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized'], 401));

            return;
        }

        preg_match('#^Bearer (.+)$#', $authorization, $matches);
        $token = $matches[1];

        if (!$token) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized'], 401));

            return;
        }

        try {
            $jwe = $this->jwt->decryptToken($token);
            $payload = json_decode($jwe->getPayload(), true);
            $userId = $payload['user_id'];
            $userEmail = $payload['user_email'];
        } catch (\Throwable $exception) {
            $event->setResponse(new JsonResponse(['error' => 'Unauthorized'], 401));

            return;
        }

        $user = $this->userRepository->find($userId);

        if (!$user || $user->getEmail() !== $userEmail) {
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
}
