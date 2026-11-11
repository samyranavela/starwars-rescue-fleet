<?php

declare(strict_types=1);

namespace App\Tests\Functional\Application\Fleet;

use App\Domain\Fleet\AssembleAFleet;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\Fleets;
use App\Domain\Fleet\StarShip;
use App\Tests\Support\Http\HttpFixtures;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\Callback;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AssembleAFleetTest extends KernelTestCase
{
    #[Test]
    #[HttpFixtures('swapi/various_sizes')]
    public function should_assemble_a_fleet_for_1050_passengers(): void
    {
        $numberOfPassengers = 1050;

        $fleets = self::getContainer()->get(Fleets::class);
        $assembleAFleet = self::getContainer()->get(AssembleAFleet::class);

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
