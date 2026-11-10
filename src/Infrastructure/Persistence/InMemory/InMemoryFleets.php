<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\Fleets;
use OutOfBoundsException;
use Symfony\Component\Uid\Uuid;

final class InMemoryFleets implements Fleets
{
    public function __construct(private array $fleets = [])
    {
    }

    public function getById(Uuid $id): Fleet
    {
        return $this->fleets[$id->toString()] ?? throw new OutOfBoundsException('Fleet not found with id '.$id);
    }

    public function save(Fleet $fleet): Fleet
    {
        return $this->fleets[$fleet->id->toString()] = $fleet;
    }
}
