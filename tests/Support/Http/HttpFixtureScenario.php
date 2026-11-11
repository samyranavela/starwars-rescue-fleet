<?php

declare(strict_types=1);

namespace App\Tests\Support\Http;

/**
 * Scenario of the test being run, set by HttpFixturesExtension before each test.
 */
final class HttpFixtureScenario
{
    private static ?string $current = null;

    public static function use(?string $scenario): void
    {
        self::$current = $scenario;
    }

    public static function current(): ?string
    {
        return self::$current;
    }
}
