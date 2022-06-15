<?php

namespace App\Controller;

use App\Entity\User;
use App\Normalizer\Callbacks;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Throwable;

class UserController extends BaseController
{
    /**
     * @Route("/user", name="user_create", methods={"POST"}, options={"auth": false})
     */
    public function createSelf(Request $request): Response
    {
        $user = new User();

        try {
            $this->entityManager->persist($user->update($request->request->all()));
            $this->entityManager->flush();
        } catch (Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return $this->prepareJsonResponse($user);
    }

    /**
     * @Route("/user", name="user_view", methods={"GET"})
     */
    public function getSelf(): Response
    {
        return $this->prepareJsonResponse($this->getUser());
    }

    /**
     * @Route("/user", name="user_update", methods={"PATCH"})
     */
    public function updateSelf(EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            $this->entityManager->persist($this->getUser()->update($request->request->all()));
            $this->entityManager->flush();
        } catch (Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return $this->prepareJsonResponse($this->getUser());
    }

    /**
     * @Route("/user", name="user_delete", methods={"DELETE"})
     */
    public function deleteSelf(EntityManagerInterface $entityManager, Request $request): Response
    {
        $this->entityManager->remove($this->getUser());
        $this->entityManager->flush();

        return $this->prepareJsonResponse($this->getUser());
    }

    /**
     * @throws ExceptionInterface
     */
    public function prepareJsonResponse(User $user): Response
    {
        return $this->json($this->normalizer->normalize(
            $user,
            null,
            [
                AbstractNormalizer::ATTRIBUTES => ['email', 'birthday', 'home', 'work', 'phoneNumber', 'name'],
                AbstractNormalizer::CALLBACKS => [
                    'birthday' => Callbacks::DATETIME_TO_DATE,
                ],
            ]
        ));
    }
}
