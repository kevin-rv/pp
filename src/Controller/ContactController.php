<?php


namespace App\Controller;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ContactController extends BaseController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer, UserRepository $userRepository, ContactRepository $contactRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct($requestStack);
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
    }
    /**
     * @Route("/contact", name="contact_create", methods={"POST"})
     */
    public function createContact(int $userId, Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['user' => $this->getUser(), 'id' => $userId]);

        if (null === $user) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $contact = new contact();

        $contact->update($request->request->all());
        $contact->setUser($user);

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$contact]);
    }

    /**
     * @Route("/contacts", name="contact_list", methods={"GET"})
     */
    public function getAllContact(int $userId): Response
    {
        $user = $this->userRepository->findOneBy(['user' => $this->getUser(), 'id' => $userId]);

        if (null === $user) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse($this->contactRepository->findAllContactByUser($this->getUser()->getId(), $userId));
    }


    /**
     * @Route("/contact/{contactId}", name="contact", methods={"GET"})
     */
    public function getOneContact(int $userId, int $contactId): Response
    {
        $contact = $this->contactRepository->findOneContactByUser($userId, $contactId);

        if (null === $contact) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse([$contact]);
    }

    /**
     * @Route("/contact/{contactId}", name="contact_update", methods={"PATCH"})
     */
    public function updateContact(int $userId, int $contactId, Request $request): Response
    {
        $contact = $this->contactRepository->findOneContactByUser($userId, $contactId);

        if (null === $contact) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $contact->update($request->request->all());
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$contact]);
    }

    /**
     * @Route("/contact/{contactId}", name="contact_delete", methods={"DELETE"})
     */
    public function deleteContact(int $userId, int $contactId): Response
    {
        $contact = $this->contactRepository->findOneContactByUser($userId, $contactId);

        if (null === $contact) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $this->entityManager->remove($contact);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$contact]);
    }
    /**
     * @param Contact[] $contacts
     */
    public function prepareJsonResponse(array $contacts): JsonResponse
    {
        return $this->json($this->serializer->normalize(
            $contacts,
            null,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['user']]
        ));
    }
}
