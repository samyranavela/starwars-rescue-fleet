<?php

declare(strict_types=1);

namespace App\UI\API\Fleet;

use App\Domain\Fleet\AssembleAFleet;
use App\Domain\Fleet\Fleets;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Uid\Uuid;

#[AsController]
#[Route("rescueFleets")]
final readonly class RescueFleetController
{
    public function __construct(
        private AssembleAFleet $fleetAssembler,
        private Fleets $fleets,
    )
    {
    }

    #[Route(methods: ['POST'])]
    public function assembleAFleet(#[MapRequestPayload] RescueFleetRequest $rescueFleetRequest): JsonResponse
    {
        $fleet = $this->fleetAssembler->forPassengers($rescueFleetRequest->numberOfPassengers);

        return new JsonResponse($fleet, Response::HTTP_CREATED);
    }

    #[Route(
        path: '/{id}',
        requirements: [
            'id' => Requirement::UUID_V7,
        ],
        methods: ['GET'],
    )]
    public function getFleetById(Uuid $id): JsonResponse
    {
        return new JsonResponse($this->fleets->getById($id));
    }
}
