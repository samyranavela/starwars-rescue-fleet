<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Swapi;

use App\Domain\Fleet\StarShip;
use App\Domain\Fleet\StarShipInventory;
use App\Tests\Support\Http\HttpFixtures;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SwapiClientTest extends KernelTestCase
{
    private StarShipInventory $starShipInventory;

    protected function setUp(): void
    {
        $this->starShipInventory = self::getContainer()->get(StarShipInventory::class);
    }

    #[Test]
    #[HttpFixtures('swapi/two_pages')]
    public function should_return_the_starship_inventory(): void
    {
        $starships = $this->starShipInventory->starShips();
        $expected = collect([
            new StarShip("CR90 corvette", 600, 3000000),
            new StarShip("Slave 1", 6, 70000),
            new StarShip("Death Star", 843342, 1000000000000),
        ]);

        self::assertEquals($expected, $starships);
    }
}
