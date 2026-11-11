<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Cache;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\Fleets;
use OutOfBoundsException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

final readonly class CacheFleets implements Fleets
{
    private const string KEY_PREFIX = 'fleet.';

    public function __construct(private CacheItemPoolInterface $cache)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getById(Uuid $id): Fleet
    {
        $item = $this->cache->getItem($this->keyFor($id));

        return $item->isHit() ? $item->get() : throw new OutOfBoundsException('Fleet not found with id '.$id);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function save(Fleet $fleet): Fleet
    {
        $this->cache->save($this->cache->getItem($this->keyFor($fleet->id))->set($fleet));

        return $fleet;
    }

    private function keyFor(Uuid $id): string
    {
        return self::KEY_PREFIX.$id->toRfc4122();
    }
}
