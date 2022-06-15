<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\JWT;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessController extends BaseController
{
    /**
     * @Route("/auth", name="auth_create", methods={"POST"}, options={"auth": false})
     */
    public function auth(Request $request, JWT $jwt, UserRepository $userRepository): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$user->isPasswordValid($password)) {
            return $this->json(['error' => 'Bad credentials'], 400);
        }

        return $this->json($jwt->generateJWT($user));
    }

    /**
     * @Route("/auth", name="auth_renew", methods={"PATCH"}, options={"auth": false})
     */
    public function authUpdate(Request $request, JWT $jwt, UserRepository $userRepository): Response
    {
        return $this->json($jwt->generateJWT($this->getUser()));
    }
}
