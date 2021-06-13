<?php

namespace App\Controller;

use App\Entity\Planning;
use App\Repository\PlanningRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Throwable;

class PlanningController extends BaseController
{
    /**
     * @var PlanningRepository
     */
    private $planningRepository;

    public function __construct(
        RequestStack $requestStack,
        NormalizerInterface $normalizer,
        EntityManagerInterface $entityManager,
        PlanningRepository $planningRepository
    ) {
        parent::__construct($requestStack, $normalizer, $entityManager);
        $this->planningRepository = $planningRepository;
    }

    /**
     * @Route("/planning", name="planning_create", methods={"POST"})
     */
    public function createPlanning(Request $request): Response
    {
        $planning = new Planning();
        try {
            $planning->update($request->request->all());
            $planning->setUser($this->getUser());

            $searchedPlanning = $this->planningRepository->findOneBy([
                'name' => $planning->getName(),
                'user' => $planning->getUser(),
            ]);

            if ($searchedPlanning) {
                return $this->json(['error' => 'name already exist'], 409);
            }
        } catch (Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        $this->entityManager->persist($planning);
        $this->entityManager->flush();

        return $this->prepareJsonResponse($planning);
    }

    /**
     * @Route("/planning/{planningId}", name="planning", methods={"GET"})
     */
    public function getOnePlanning(int $planningId): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        return $this->prepareJsonResponse($planning);
    }

    /**
     * @Route("/planning", name="planning_list", methods={"GET"})
     */
    public function getAllPlanning(): Response
    {
        return $this->prepareJsonResponse($this->planningRepository->findBy(['user' => $this->getUser()]));
    }

    /**
     * @Route("/planning/{planningId}", name="planning_update", methods={"PATCH"})
     */
    public function updatePlanning(int $planningId, Request $request): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $planning->update($request->request->all());
        $this->entityManager->persist($planning);
        $this->entityManager->flush();

        return $this->prepareJsonResponse($planning);
    }

    /**
     * @Route("/planning/{planningId}", name="planning_delete", methods={"DELETE"})
     */
    public function deletePlanning(int $planningId): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $this->entityManager->remove($planning);
        $this->entityManager->flush();

        return $this->prepareJsonResponse($planning);
    }

    /**
     * @param Planning[]|Planning $plannings
     *
     * @throws ExceptionInterface
     */
    public function prepareJsonResponse($plannings): JsonResponse
    {
        return $this->json($this->normalizer->normalize(
            $plannings,
            null,
            [
                AbstractNormalizer::IGNORED_ATTRIBUTES => ['events', 'tasks', 'user'],
            ]
        ));
    }
}
