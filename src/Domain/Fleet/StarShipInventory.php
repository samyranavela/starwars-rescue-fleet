<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use Illuminate\Support\Collection;

interface StarShipInventory
{
    /**
     * @return Collection<StarShip>
     */
    public function starShips(): Collection;
}
