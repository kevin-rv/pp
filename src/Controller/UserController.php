<?php


namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends BaseController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer)
    {
        parent::__construct($requestStack);
        $this->serializer = $serializer;
    }

    /**
     * @Route("/user", name="user_create", methods={"POST"}, options={"auth": false})
     */
    public function createSelf(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = new User();
        try {
            $entityManager->persist($user->update($request->request->all()));
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
        $entityManager->flush();

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
    public function updateSelf(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer): Response
    {
        try {
            $entityManager->persist($this->getUser()->update($request->request->all()));
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }
        $entityManager->flush();

        return $this->prepareUserJsonResponse($this->getUser());
    }

    /**
     * @Route("/user", name="user_delete", methods={"DELETE"})
     */
    public function deleteSelf(EntityManagerInterface $entityManager, Request $request, SerializerInterface $serializer): Response
    {
        $entityManager->remove($this->getUser());
        $entityManager->flush();

        return $this->prepareUserJsonResponse($this->getUser());
    }

    public function prepareUserJsonResponse(User $user): Response
    {
        return $this->json($this->serializer->normalize(
            $user,
            null,
            [AbstractNormalizer::ATTRIBUTES => ['email', 'birthday', 'home', 'work', 'phoneNumber', 'name']]
        ));
    }
}
