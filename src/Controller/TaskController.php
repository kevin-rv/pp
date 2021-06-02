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

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer, TaskRepository $taskRepository)
    {
        parent::__construct($requestStack);
        $this->serializer = $serializer;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @Route("/planning/{planningId}/task", name="task_create", methods={"POST"})
     */
    public function createTask(int $planningId, PlanningRepository $planningRepository, EntityManagerInterface $entityManager, Request $request, PlanningController $planningController): Response
    {
        $planning = $planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $task = new task();

        $task->update($request->request->all());
        $task->setPlanning($planning);

        $entityManager->persist($task);
        $entityManager->flush();

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @Route("/planning/{planningId}/task", name="task_list", methods={"GET"})
     */
    public function getAllTask(int $planningId): Response
    {
        return $this->prepareJsonResponse($this->taskRepository->findBy(['planning' => $planningId]));
    }

    /**
     * @Route("/planning/{planningId}/task/{taskId}", name="task", methods={"GET"})
     */
    public function getOneTask(int $planningId, int $taskId): Response
    {
        $task = $this->taskRepository->findOneBy(['planning' => $planningId, 'id' => $taskId]);

        if (null === $task) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @Route("/planning/{planningId}/task/{taskId}", name="task_update", methods={"PATCH"})
     */
    public function updateTask(int $planningId, int $taskId, EntityManagerInterface $entityManager, Request $request): Response
    {
        $task = $this->taskRepository->findOneBy(['planning' => $planningId, 'id' => $taskId]);

        if (null === $task) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $task->update($request->request->all());
        $entityManager->persist($task);
        $entityManager->flush();

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @Route("/planning/{planningId}/task/{taskId}", name="task_delete", methods={"DELETE"})
     */
    public function deleteTask(int $planningId, int $taskId, EntityManagerInterface $entityManager): Response
    {
        $task = $this->taskRepository->findOneBy(['user' => $this->getUser(), 'planning' => $planningId, 'id' => $taskId]);

        if (null === $task) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $entityManager->remove($task);
        $entityManager->flush();

        return $this->prepareJsonResponse([$task]);
    }

    /**
     * @param Task[] $task
     */
    public function prepareJsonResponse(array $task): JsonResponse
    {
        $normalizeDateTimeToDate = function ($innerObject) {
            if (!$innerObject instanceof \DateTimeInterface) {
                return null;
            }

            return $innerObject->format('Y-m-d');
        };

        return $this->json($this->serializer->normalize(
            $task,
            null,
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['planning'],
                AbstractNormalizer::CALLBACKS => [
                    'done' => $normalizeDateTimeToDate,
                    'doneLimitDate' => $normalizeDateTimeToDate,
                ]
            ]
        ));
    }
}
