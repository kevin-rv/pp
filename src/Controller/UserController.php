<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

class UserController extends BaseController
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    public function __construct(RequestStack $requestStack, NormalizerInterface $normalizer)
    {
        parent::__construct($requestStack);
        $this->normalizer = $normalizer;
    }

    /**
     * @Route("/user", name="user_create", methods={"POST"}, options={"auth": false})
     */
    public function createSelf(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = new User();
        try {
            $entityManager->persist($user->update($request->request->all()));
            $entityManager->flush();
        } catch (Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return $this->prepareUserJsonResponse($user);
    }

    /**
     * @Route("/user", name="user_view", methods={"GET"})
     */
    public function getSelf(): Response
    {
        return $this->prepareUserJsonResponse($this->getUser());
    }

    /**
     * @Route("/user", name="user_update", methods={"PATCH"})
     */
    public function updateSelf(EntityManagerInterface $entityManager, Request $request): Response
    {
        try {
            $entityManager->persist($this->getUser()->update($request->request->all()));
            $entityManager->flush();
        } catch (Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        return $this->prepareUserJsonResponse($this->getUser());
    }

    /**
     * @Route("/user", name="user_delete", methods={"DELETE"})
     */
    public function deleteSelf(EntityManagerInterface $entityManager, Request $request): Response
    {
        $entityManager->remove($this->getUser());
        $entityManager->flush();

        return $this->prepareUserJsonResponse($this->getUser());
    }

    /**
     * @throws ExceptionInterface
     */
    public function prepareUserJsonResponse(User $user): Response
    {
        $normalizeDateTimeToDate = function ($innerObject) {
            if (!$innerObject instanceof DateTimeInterface) {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }

            return $innerObject->format('Y-m-d');
        };

        return $this->json($this->normalizer->normalize(
            $user,
            null,
            [AbstractNormalizer::ATTRIBUTES => ['email', 'birthday', 'home', 'work', 'phoneNumber', 'name'],
                AbstractNormalizer::CALLBACKS => ['birthday' => $normalizeDateTimeToDate], ]
        ));
    }
}
