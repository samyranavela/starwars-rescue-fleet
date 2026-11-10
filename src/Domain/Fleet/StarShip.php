<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

final readonly class StarShip
{

    public function __construct(
        public string $name,
        public int $capacity,
        public int $cargoCapacity,
    )
    {
    }
}
