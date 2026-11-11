<?php

declare(strict_types=1);

namespace App\Application\Fleet;

use App\Domain\Fleet\AssembleAFleet;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\Fleets;
use App\Domain\Fleet\StarShip;
use App\Domain\Fleet\StarShipInventory;
use Illuminate\Support\Collection;

final readonly class FleetAssembler implements AssembleAFleet
{
    public function __construct(
        private StarShipInventory $starShipInventory,
        private Fleets $fleets,
    )
    {
    }

    public function forPassengers(int $numberOfPassengers): Fleet
    {
        $starShips = $this->getStarShipsHavingPassengersCapacity();
        $rescueStarShips = $this->selectStarShips($numberOfPassengers, $starShips);

        return $this->fleets->save(Fleet::from($rescueStarShips));
    }

    private function getStarShipsHavingPassengersCapacity(): Collection
    {
        return $this->starShipInventory
            ->starShips()
            ->filter(static fn (StarShip $starShip): bool => $starShip->capacity > 0)
            ->sortBy(static fn (StarShip $starShip): int => $starShip->capacity)
        ;
    }

    private function selectStarShips(int $numberOfPassengers, Collection $starShips): Collection
    {
        $starShips = $starShips
            ->filter(static fn (StarShip $starShip): bool => ($starShip->cargoCapacity <=> AssembleAFleet::MINIMAL_CARGO_CAPACITY) >= 0)
        ;

        $rescueStarShips = Collection::empty();
        if ($starShips->isEmpty()) {
            return $rescueStarShips;
        }

        while ($numberOfPassengers > 0) {
            $starShip = $starShips->shift();
            $numberOfPassengers -= $starShip->capacity;

            $rescueStarShips->push($starShip);
        }

        return $rescueStarShips;
    }
}
