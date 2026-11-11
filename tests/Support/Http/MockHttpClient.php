<?php

declare(strict_types=1);

namespace App\Tests\Support\Http;

use JsonException;
use LogicException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class MockHttpClient
{
    public function __construct(
        #[Autowire(param: 'kernel.project_dir')]
        private string $projectDir,
        #[Autowire(env: 'bool:default::RECORD_HTTP_FIXTURES')]
        private bool $record,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    public function __invoke(string $method, string $url, array $options = []): ResponseInterface
    {
        $scenario = HttpFixtureScenario::current()
            ?? throw new LogicException('No HTTP fixture scenario: HttpFixturesExtension is not registered in phpunit.dist.xml, or the HTTP call is not made from a test method.');

        $file = sprintf('%s/tests/Fixtures/Http/%s/%s', $this->projectDir, $scenario, $this->fixtureName($method, $url, $options));

        if (!is_file($file)) {
            if (!$this->record) {
                throw new LogicException(sprintf('Missing HTTP fixture "%s" for %s %s (run with RECORD_FIXTURES=1 to record it).', $file, $method, $url));
            }

            $this->record($file, $method, $url, $options);
        }

        $fixture = json_decode(file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);

        return new JsonMockResponse($fixture['body'], ['http_code' => $fixture['status']]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws JsonException
     */
    private function record(string $file, string $method, string $url, array $options): void
    {
        $response = new NativeHttpClient()->request($method, $url, $options);

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        file_put_contents($file, json_encode([
            'status' => $response->getStatusCode(),
            'body' => $response->toArray(false),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n");
    }

    /**
     * @throws JsonException
     */
    private function fixtureName(string $method, string $url, array $options): string
    {
        $parts = parse_url($url);
        $query = $parts['query'] ?? http_build_query($options['query'] ?? []);

        $name = $method.'_'.$this->slug(($parts['host'] ?? '').($parts['path'] ?? ''));
        if ('' !== $query) {
            $name .= '__'.$this->slug($query);
        }

        $body = $options['json'] ?? $options['body'] ?? null;
        if (null !== $body) {
            $name .= '_'.substr(sha1(is_string($body) ? $body : json_encode($body, JSON_THROW_ON_ERROR)), 0, 8);
        }

        return $name.'.json';
    }

    private function slug(string $value): string
    {
        return trim(preg_replace('/[^A-Za-z0-9.-]+/', '_', $value), '_');
    }
}
