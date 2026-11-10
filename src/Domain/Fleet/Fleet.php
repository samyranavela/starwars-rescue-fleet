<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use Illuminate\Support\Collection;
use Symfony\Component\Uid\Uuid;

final readonly class Fleet
{
    public function __construct(
        public Uuid $id,
        public Collection $starships,
    )
    {
    }

    public static function from(Collection $starShips): self
    {
        return new self(
            Uuid::v7(),
            $starShips,
        );
    }
}
