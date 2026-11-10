<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use Symfony\Component\Uid\Uuid;

interface Fleets
{
    public function getById(Uuid $id): Fleet;

    public function save(Fleet $fleet): Fleet;
}
