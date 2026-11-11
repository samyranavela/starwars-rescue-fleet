<?php

declare(strict_types=1);

namespace App\Infrastructure\Swapi\Model;

use Illuminate\Support\Collection;
use Uri\Rfc3986\Uri;

final readonly class SwapiResponse
{
    /** @var Collection<int, SwapiStarShip> */
    public Collection $results;

    /**
     * The Serializer denormalizes a `Collection<int, X>` constructor argument into a plain array of X,
     * so the constructor takes the array and wraps it itself.
     *
     * @param list<SwapiStarShip> $results
     */
    public function __construct(
        private(set) int $count,
        private(set) ?Uri $next,
        array $results,
    ) {
        $this->results = new Collection($results);
    }
}
