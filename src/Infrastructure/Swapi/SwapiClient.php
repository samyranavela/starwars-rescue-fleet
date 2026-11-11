<?php

declare(strict_types=1);

namespace App\Infrastructure\Swapi;

use App\Domain\Fleet\StarShip;
use App\Domain\Fleet\StarShipInventory;
use App\Infrastructure\Swapi\Model\SwapiResponse;
use App\Infrastructure\Swapi\Model\SwapiStarShip;
use Illuminate\Support\Collection;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Uri\Rfc3986\Uri;

use function Symfony\Component\String\u;

final readonly class SwapiClient implements StarShipInventory
{
    public function __construct(
        #[Target('swapi.client')]
        private HttpClientInterface $httpClient,
        private SerializerInterface $serializer,
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ExceptionInterface
     */
    public function starShips(): Collection
    {
        $starships = Collection::empty();

        $nextPageUrl = 'starships';
        do {
            $swapiResponse = $this->getStarShipFromSwapi($nextPageUrl);
            $starships = $starships->merge($this->convertSwapiResponseToStarShips($swapiResponse));

            $nextPageUrl = $swapiResponse->next?->toString();
        } while (null !== $nextPageUrl);

        return $starships;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function getStarShipFromSwapi(?string $nextPageUrl): SwapiResponse
    {
        $response = $this->httpClient->request('GET', $nextPageUrl);

        return $this->serializer->deserialize($response->getContent(), SwapiResponse::class, JsonEncoder::FORMAT);
    }

    private function convertSwapiResponseToStarShips(SwapiResponse $swapiResponse): Collection
    {
        return $swapiResponse->results
            ->filter($this->hasValidPassengersValue(...))
            ->transform($this->toStarShip(...))
        ;
    }

    private function hasValidPassengersValue(SwapiStarShip $starShip): bool
    {
        return $starShip->passengers !== null
            && !$this->invalidCapacitiesValues()->contains($starShip->passengers)
            && !$this->invalidCapacitiesValues()->contains($starShip->cargoCapacity);
    }

    private function invalidCapacitiesValues(): Collection
    {
        return collect(["n/a", "unknown"]);
    }

    private function toStarShip(SwapiStarShip $swapiStarShip): StarShip
    {
        return new StarShip(
            $swapiStarShip->name,
            $this->parseInt($swapiStarShip->passengers),
            $this->parseInt($swapiStarShip->cargoCapacity),
        );
    }

    private function parseInt(?string $value): int
    {
        if (null === $value) {
            return 0;
        }
        $value = u($value)->replace(',', '')->toString();

        return is_numeric($value) ? (int) $value : 0;
    }
}
