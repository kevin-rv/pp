<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\PlanningRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class TaskController extends BaseController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var TaskRepository
     */
    private $taskRepository;
    /**
     * @var PlanningRepository
     */
    private $planningRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer, TaskRepository $taskRepository, PlanningRepository $planningRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct($requestStack);
        $this->serializer = $serializer;
        $this->taskRepository = $taskRepository;
        $this->planningRepository = $planningRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/planning/{planningId}/task", name="task_create", methods={"POST"})
     */
    public function createTask(int $planningId, Request $request): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $task = new task();

        $task->update($request->request->all());
        $task->setPlanning($planning);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @Route("/planning/{planningId}/task", name="task_list", methods={"GET"})
     */
    public function getAllTask(int $planningId): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse($this->taskRepository->findAllTaskByUserPlanning($this->getUser()->getId(), $planningId));
    }

    /**
     * @Route("/planning/{planningId}/task/{taskId}", name="task", methods={"GET"})
     */
    public function getOneTask(int $planningId, int $taskId): Response
    {
        $task = $this->taskRepository->findOneTaskByUserPlanning($this->getUser()->getId(), $planningId, $taskId);

        if (null === $task) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @Route("/planning/{planningId}/task/{taskId}", name="task_update", methods={"PATCH"})
     */
    public function updateTask(int $planningId, int $taskId, Request $request): Response
    {
        $task = $this->taskRepository->findOneTaskByUserPlanning($this->getUser()->getId(), $planningId, $taskId);

        if (null === $task) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $task->update($request->request->all());
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @Route("/planning/{planningId}/task/{taskId}", name="task_delete", methods={"DELETE"})
     */
    public function deleteTask(int $planningId, int $taskId): Response
    {
        $task = $this->taskRepository->findOneTaskByUserPlanning($this->getUser()->getId(), $planningId, $taskId);

        if (null === $task) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $this->entityManager->remove($task);
        $this->entityManager->flush();

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @param Task[] $tasks
     */
    public function prepareJsonResponse(array $tasks): JsonResponse
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
            $tasks,
            null,
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['planning'],
                AbstractNormalizer::CALLBACKS => [
                    'done' => $normalizeDateTimeToDate,
                    'doneLimitDate' => $normalizeDateTimeToDate,
                ],
            ]
        ));
    }
}
