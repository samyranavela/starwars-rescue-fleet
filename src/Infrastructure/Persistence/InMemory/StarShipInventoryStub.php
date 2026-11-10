<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Fleet\StarShip;
use App\Domain\Fleet\StarShipInventory;
use Illuminate\Support\Collection;

final readonly class StarShipInventoryStub implements StarShipInventory
{
    private Collection $starShips;

    public function __construct(?Collection $starShips = null)
    {
        $this->starShips = $starShips ?: collect([
            new StarShip("X-Wing", 0, 100),
            new StarShip("Millennium Falcon", 6, 100000),
            new StarShip("Rebel transport", 90, 80000),
            new StarShip("Mon Calamari Star Cruisers", 1200, 200000),
            new StarShip("CR90 corvette", 600, 300000),
            new StarShip("EF76 Nebulon-B escort frigate", 800, 350000),
        ]);
    }


    public function starShips(): Collection
    {
        return $this->starShips;
    }
}
