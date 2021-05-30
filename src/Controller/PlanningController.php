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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * HTTP Verb	CRUD	Entire Collection (e.g. /customers)	Specific Item (e.g. /customers/{id}).
 *
 * POST
 * Create	201 (Created), 'Location' header with link to /customers/{id} containing new ID.
 * 404 (Not Found), 409 (Conflict) if resource already exists..
 *
 * GET
 * Read	200 (OK), list of customers. Use pagination, sorting and filtering to navigate big lists.
 * 200 (OK), single customer. 404 (Not Found), if ID not found or invalid.
 *
 * PUT
 * Update/Replace	405 (Method Not Allowed), unless you want to update/replace every resource in the entire collection.
 * 200 (OK) or 204 (No Content). 404 (Not Found), if ID not found or invalid.
 *
 * PATCH	Update/Modify	405 (Method Not Allowed), unless you want to modify the collection itself.
 * 200 (OK) or 204 (No Content). 404 (Not Found), if ID not found or invalid.
 *
 * DELETE	Delete	405 (Method Not Allowed), unless you want to delete the whole collection—not often desirable.
 * 200 (OK). 404 (Not Found), if ID not found or invalid.
 */
class PlanningController extends BaseController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var PlanningRepository
     */
    private $planningRepository;

    public function __construct(RequestStack $requestStack, SerializerInterface $serializer, PlanningRepository $planningRepository)
    {
        parent::__construct($requestStack);
        $this->serializer = $serializer;
        $this->planningRepository = $planningRepository;
    }

    /**
     * @Route("/planning", name="planning_list", methods={"GET"})
     */
    public function getAllPlanning(): Response
    {
        return $this->prepareJsonResponse($this->planningRepository->findBy(['user' => $this->getUser()]));
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

        return $this->prepareJsonResponse([$planning]);
    }

    /**
     * @Route("/planning", name="planning_create", methods={"POST"})
     */
    public function createPlanning(EntityManagerInterface $entityManager, Request $request): Response
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
                return $this->json(['error' => 'name already exist'], 400);
            }
        } catch (\Throwable $exception) {
            return $this->json(['error' => $exception->getMessage()], 400);
        }

        $entityManager->persist($planning);
        $entityManager->flush();

        return $this->prepareJsonResponse([$planning]);
    }

    /**
     * @Route("/planning/{planningId}", name="planning_update", methods={"PATCH"})
     */
    public function updatePlanning(int $planningId, EntityManagerInterface $entityManager, Request $request): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $planning->update($request->request->all());
        $entityManager->persist($planning);
        $entityManager->flush();

        return $this->prepareJsonResponse([$planning]);
    }

    /**
     * @Route("/planning/{planningId}", name="planning_delete", methods={"DELETE"})
     */
    public function deletePlanning(int $planningId, EntityManagerInterface $entityManager): Response
    {
        $planning = $this->planningRepository->findOneBy(['user' => $this->getUser(), 'id' => $planningId]);

        if (null === $planning) {
            return $this->json(['error' => 'Not Found'], 404);
        }

        $entityManager->remove($planning);
        $entityManager->flush();

        return $this->prepareJsonResponse([$planning]);
    }

    /**
     * @param Planning[] $plannings
     */
    public function prepareJsonResponse(array $plannings): JsonResponse
    {
        return $this->json($this->serializer->normalize(
            $plannings,
            null,
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['events', 'tasks', 'user']]
        ));
    }
}
