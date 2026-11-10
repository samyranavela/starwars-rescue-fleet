<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

interface AssembleAFleet
{
    public const int MINIMAL_CARGO_CAPACITY = 100000;

    public function forPassengers(int $numberOfPassengers): Fleet;
}
