<?php

declare(strict_types=1);

namespace App\Tests\Support\Http;

use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Resolves the HTTP fixture scenario of each test (#[HttpFixtures] on the method, then on the class,
 * else "<TestClass>/<testMethod>") and hands it to MockHttpClient through HttpFixtureScenario.
 */
final class HttpFixturesExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements PreparationStartedSubscriber {
            public function notify(PreparationStarted $event): void
            {
                $test = $event->test();

                if (!$test->isTestMethod()) {
                    HttpFixtureScenario::use(null);

                    return;
                }

                $class = $test->className();
                $method = $test->methodName();

                HttpFixtureScenario::use(
                    $this->attributeOf(new ReflectionMethod($class, $method))
                    ?? $this->attributeOf(new ReflectionClass($class))
                    ?? sprintf('%s/%s', new ReflectionClass($class)->getShortName(), $method),
                );
            }

            private function attributeOf(ReflectionClass|ReflectionMethod $reflection): ?string
            {
                $attributes = $reflection->getAttributes(HttpFixtures::class);

                return [] === $attributes ? null : $attributes[0]->newInstance()->scenario;
            }
        });
    }
}
