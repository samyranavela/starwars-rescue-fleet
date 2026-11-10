<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Fleet;

use App\Application\Fleet\FleetAssembler;
use App\Domain\Fleet\AssembleAFleet;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\StarShip;
use App\Infrastructure\Persistence\InMemory\InMemoryFleets;
use App\Infrastructure\Persistence\InMemory\StarShipInventoryStub;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\TestCase;

final class AssembleAFleetTest extends TestCase
{
    public function testShouldAssembleAFleetFor1050Passengers(): void
    {
        $starShips = collect([
            new StarShip("no-passenger-ship", 0, 0),
            new StarShip("xs", 10, 1000),
            new StarShip("s", 50, 50000),
            new StarShip("m", 200, 70000),
            new StarShip("l", 800, 150000),
            new StarShip("xl", 2000, 500000),
        ]);
        $numberOfPassengers = 1050;

        $fleets = new InMemoryFleets();
        $assembleAFleet = new FleetAssembler(
            new StarShipInventoryStub($starShips),
            $fleets,
        );

        $fleet = $assembleAFleet->forPassengers($numberOfPassengers);

        self::assertThat($fleet, $this->enoughCapacityForThePassengers($numberOfPassengers));
        self::assertThat($fleet, $this->allStarShipHasPassengersCapacity());
        self::assertThat($fleet, $this->allHasEnoughCargoCapacity());

        self::assertEquals($fleets->getById($fleet->id), $fleet);
    }

    private function enoughCapacityForThePassengers(int $numberOfPassengers): Callback
    {
        return self::callback(static fn (Fleet $fleet): true => $fleet
                ->starships
                ->map(static fn (StarShip $starShip): int => $starShip->capacity)
                ->sum() >= $numberOfPassengers,
        );
    }

    private function allStarShipHasPassengersCapacity(): Callback
    {
        return self::callback(static fn (Fleet $fleet): true
            => $fleet
            ->starships
            ->filter(static fn (StarShip $starShip): true => $starShip->capacity > 0)
            ->isNotEmpty(),
        );
    }

    private function allHasEnoughCargoCapacity(): Callback
    {
        return self::callback(static fn (Fleet $fleet): true
            => $fleet
            ->starships
            ->filter(static fn (StarShip $starShip): true => ($starShip->cargoCapacity <=> AssembleAFleet::MINIMAL_CARGO_CAPACITY) >= 0)
            ->isNotEmpty(),
        );
    }
}
