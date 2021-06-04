<?php


namespace App\Controller;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class EventController extends BaseController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var PlanningRepository
     */
    private $planningRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EventRepository
     */
    private $eventRepository;

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer, EventRepository $eventRepository, PlanningRepository $planningRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct($requestStack);
        $this->serializer = $serializer;
        $this->planningRepository = $planningRepository;
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
    }


    /**
     * @Route("/planning/{planningId}/event", name="event_create", methods={"POST"})
     */
    public function createEvent(int $planningId, Request $request): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $event = new event();

        $event->update($request->request->all());
        $event->setPlanning($planning);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$event]);
    }

    /**
     * @Route("/planning/{planningId}/event", name="event_list", methods={"GET"})
     */
    public function getAllEvent(int $planningId): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse($this->eventRepository->findAllEventByUserPlanning($this->getUser()->getId(), $planningId));
    }

    /**
     * @Route("/planning/{planningId}/event/{eventId}", name="event", methods={"GET"})
     */
    public function getOneEvent(int $planningId, int $eventId): Response
    {
        $event = $this->eventRepository->findOneEventByUserPlanning($this->getUser()->getId(), $planningId, $eventId);

        if (null === $event) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse([$event]);
    }

    /**
     * @Route("/planning/{planningId}/event/{eventId}", name="event_update", methods={"PATCH"})
     */
    public function updateEvent(int $planningId, int $eventId, Request $request): Response
    {
        $event = $this->eventRepository->findOneEventByUserPlanning($this->getUser()->getId(), $planningId, $eventId);

        if (null === $event) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $event->update($request->request->all());
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$event]);
    }

    /**
     * @Route("/planning/{planningId}/event/{eventId}", name="event_delete", methods={"DELETE"})
     */
    public function deleteEvent(int $planningId, int $eventId): Response
    {
        $event = $this->eventRepository->findOneEventByUserPlanning($this->getUser()->getId(), $planningId, $eventId);

        if (null === $event) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$event]);
    }

    /**
     * @param Event[] $events
     */
    public function prepareJsonResponse(array $events): JsonResponse
    {
        $normalizeDateTimeToDate = function ($innerObject) {
            if (!$innerObject instanceof \DateTimeInterface) {
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
            }

            return $innerObject->format('Y-m-d');
        };

        return $this->json($this->serializer->normalize(
            $events,
            null,
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['planning'],
                AbstractNormalizer::CALLBACKS => [
                    'startDatetime' => $normalizeDateTimeToDate,
                    'endDatetime' => $normalizeDateTimeToDate,
                ]
            ]
        ));
    }
}
