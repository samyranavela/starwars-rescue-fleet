<?php

declare(strict_types=1);

namespace App\UI\API\Fleet;

use Symfony\Component\Validator\Constraints\Positive;

final readonly class RescueFleetRequest
{
    public function __construct(
        #[Positive]
        public int $numberOfPassengers,
    )
    {
    }
}
