<?php

namespace App\Controller;

use App\Repository\PlanningRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * HTTP Verb	CRUD	Entire Collection (e.g. /customers)	Specific Item (e.g. /customers/{id})
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


class PlanningController extends AbstractController
{
    /**
     * @Route("/planning", name="planning_list", methods={"GET"})
     */
    public function getAllPlanning(PlanningRepository $planningRepository, SerializerInterface $serializer): Response
    {
        // récupérer tous les plannings et les renvoyer à l'utilisateur

        $plannings = $planningRepository->findAll();

        return $this->json($serializer->normalize(
            $plannings,
            'json',
            [AbstractNormalizer::IGNORED_ATTRIBUTES => ['events', 'tasks', 'user']]
        ));
    }

    /**
     * @Route("/planning/{planningId}", name="planning", methods={"GET"})
     */
    public function getOnePlanning(int $planningId): Response
    {
        // récupérer es plannings et le renvoyer

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlanningController.php',
        ]);
    }

    /**
     * @Route("/planning", name="planning_create", methods={"POST"})
     */
    public function createPlanning(): Response
    {
        // créer un planning et le retourner

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlanningController.php',
        ]);
    }

    /**
     * @Route("/planning/{planningId}", name="planning_update", methods={"PATCH"})
     */
    public function updatePlanning(int $planningId): Response
    {
        // updater le planning et le retourner

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlanningController.php',
        ]);
    }

    /**
     * @Route("/planning/{planningId}", name="planning_delete", methods={"DELETE"})
     */
    public function deletePlanning(int $planningId): Response
    {
        // supprimer le planning et confirmer la suppression

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PlanningController.php',
        ]);
    }
}
