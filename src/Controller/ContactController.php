<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Normalizer\Callbacks;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContactController extends BaseController
{
    /**
     * @var ContactRepository
     */
    private $contactRepository;

    public function __construct(
        RequestStack $requestStack,
        NormalizerInterface $normalizer,
        ContactRepository $contactRepository,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($requestStack, $normalizer, $entityManager);
        $this->contactRepository = $contactRepository;
    }

    /**
     * @Route("/contact", name="contact_create", methods={"POST"})
     */
    public function createContact(Request $request): Response
    {
        $contact = new contact();

        $contact->update($request->request->all());
        $contact->setUser($this->getUser());

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->prepareJsonResponse($contact, 201);
    }

    /**
     * @Route("/contacts", name="contact_list", methods={"GET"})
     */
    public function getAllContact(): Response
    {
        return $this->prepareJsonResponse($this->contactRepository->findAllContactByUser($this->getUser()->getId()));
    }

    /**
     * @Route("/contact/{contactId}", name="contact", methods={"GET"})
     */
    public function getOneContact(int $contactId): Response
    {
        $contact = $this->contactRepository->findOneContactByUser($this->getUser()->getId(), $contactId);

        if (null === $contact) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse([$contact]);
    }

    /**
     * @Route("/contact/{contactId}", name="contact_update", methods={"PATCH"})
     */
    public function updateContact(int $contactId, Request $request): Response
    {
        $contact = $this->contactRepository->findOneContactByUser($this->getUser()->getId(), $contactId);

        if (null === $contact) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $contact->update($request->request->all());
        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $this->prepareJsonResponse($contact);
    }

    /**
     * @Route("/contact/{contactId}", name="contact_delete", methods={"DELETE"})
     */
    public function deleteContact(int $contactId): Response
    {
        $contact = $this->contactRepository->findOneContactByUser($this->getUser()->getId(), $contactId);

        if (null === $contact) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $this->entityManager->remove($contact);
        $this->entityManager->flush();

        return $this->prepareJsonResponse($contact);
    }

    /**
     * @param Contact[]|Contact $contacts
     *
     * @throws ExceptionInterface
     */
    public function prepareJsonResponse($contacts, int $status = 200): JsonResponse
    {
        return $this->json($this->normalizer->normalize(
            $contacts,
            null,
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['user', 'events'],
                AbstractNormalizer::CALLBACKS => [
                    'birthday' => Callbacks::DATETIME_TO_DATE,
                ],
            ]
        ), $status);
    }
}
