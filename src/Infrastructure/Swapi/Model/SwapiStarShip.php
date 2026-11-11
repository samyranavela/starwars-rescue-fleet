<?php

declare(strict_types=1);

namespace App\Infrastructure\Swapi\Model;

use Symfony\Component\Serializer\Attribute\SerializedName;

final readonly class SwapiStarShip
{
    public function __construct(
        private(set) string $name,
        private(set) ?string $passengers,
        #[SerializedName('cargo_capacity')]
        private(set) ?string $cargoCapacity,
    )
    {
    }

    public static function fromArray(array $starship): self
    {
        return new self(
            $starship['name'] ?? null,
            $starship['passengers'] ?? null,
            $starship['cargo_capacity'] ?? null,
        );
    }
}
