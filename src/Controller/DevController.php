<?php

namespace App\Controller;

use App\Entity\User;
use App\Kernel;
use App\Security\JWT;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DevController extends BaseController
{
    /**
     * @Route("/dev/generate-jwt-key", name="jwt_key_gen", options={"auth": false})
     */
    public function index(Kernel $kernel, JWT $jwt): Response
    {
        if ('dev' !== $kernel->getEnvironment()) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        return $this->json($jwt->generateRandomOctKey());
    }

    /**
     * @Route("/dev/generate-user-token-and-check", name="jwt_gen_test", options={"auth": false})
     */
    public function testTokenGen(Kernel $kernel, JWT $jwt): Response
    {
        if ('dev' !== $kernel->getEnvironment()) {
            return $this->json(['error' => 'Forbidden'], 403);
        }

        $user = new User();
        $user->setEmail('test@email.com');

        $token = $jwt->generateJWT($user);

        $jwe = $jwt->decryptToken($token);

        $payload = json_decode($jwe->getPayload(), true);

        return $this->json([
            'token' => $token,
            'payload' => $payload,
            'confirm_email' => 'test@email.com' === $payload['user_email'],
        ]);
    }
}
