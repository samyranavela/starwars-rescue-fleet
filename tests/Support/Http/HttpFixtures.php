<?php

declare(strict_types=1);

namespace App\Tests\Support\Http;

use Attribute;

/**
 * Selects the directory (under tests/Fixtures/Http) the HTTP mock replays its responses from.
 * On a test method it wins over the class; without it the scenario is "<TestClass>/<testMethod>".
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class HttpFixtures
{
    public function __construct(public string $scenario)
    {
    }
}
