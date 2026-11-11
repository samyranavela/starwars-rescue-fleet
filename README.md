# StarWars Rescue Fleet

PHP / Symfony port of the
[hexagonal-architecture-java-springboot](https://gitlab.com/beyondxscratch/hexagonal-architecture-java-springboot)
POC: an API that assembles a fleet of starships to rescue rebel passengers, built with a hexagonal architecture.

## Background

The original project comes from a conference talk, available in two languages:

- [L'Architecture Hexagonale par la pratique](https://www.youtube.com/watch?v=-dXN8wkN0yk) (French)
- [English version](https://www.youtube.com/watch?v=YPmKHm7G19Q)

This repository reproduces the same domain and the same hexagonal layout in PHP.

## How it works

`POST /rescueFleets` receives a number of passengers and builds a fleet from the starship inventory provided by
[SWAPI](https://swapi.dev/):

- only starships with a passenger capacity are kept;
- only those with a cargo capacity of at least **100,000** are eligible;
- starships are picked from the smallest to the largest until all passengers are covered.

The fleet is stored and can be read back with `GET /rescueFleets/{id}`.

## Requirements

- PHP **8.5** or later, with the `ext-uri` extension
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download) (optional)

## Installation

```bash
composer install
```

## Running the application

```bash
symfony serve -d          # or: php -S localhost:8000 -t public
```

The API listens on `http://localhost:8000`. Sample requests are available in `StarWarsRescueFleet.http`
(PhpStorm HTTP client, using the `dev` environment from `http-client.env.json`).

### Example

```bash
curl -i -X POST http://localhost:8000/rescueFleets \
  -H 'Content-Type: application/json' \
  -d '{"numberOfPassengers": 800}'
```

Response: `201 Created`, with a `Location` header pointing to `/rescueFleets/{id}` and a body like:

```json
{
  "id": "0192f5c0-...",
  "starships": [
    { "name": "Millennium Falcon", "capacity": 6, "cargoCapacity": 100000 }
  ]
}
```

### Configuration

| Variable    | Default                   | Purpose                        |
|-------------|---------------------------|--------------------------------|
| `SWAPI_URI` | `https://swapi.dev/api/`  | Base URL of the starships API  |

Override it in `.env.local` if needed.

## Architecture

```
src/
├── Domain/Fleet/                 Model and ports: Fleet, StarShip, Fleets, StarShipInventory, AssembleAFleet
├── Application/Fleet/            Use case: FleetAssembler (implements AssembleAFleet)
├── Infrastructure/
│   ├── Swapi/                    Driven adapter: SwapiClient (StarShipInventory) and its models
│   ├── Persistence/Cache/        Driven adapter: CacheFleets (Fleets), backed by the Symfony cache
│   └── Symfony/Serializer/       Normalizers (Collection, Uri)
└── UI/API/Fleet/                 Driving adapter: RescueFleetController and RescueFleetRequest
```

The domain depends on no adapter: dependencies point from `UI` and `Infrastructure` towards `Application` and
`Domain`.

| Java                       | PHP                                          |
|----------------------------|----------------------------------------------|
| `fleet/api/AssembleAFleet` | `Domain/Fleet/AssembleAFleet`                |
| `fleet/spi/*`              | `Domain/Fleet/Fleets`, `StarShipInventory`   |
| `InMemoryFleets` (stub)    | `CacheFleets`                                |
| `SwapiClient`              | `Infrastructure/Swapi/SwapiClient`           |
| `RescueFleetController`    | `UI/API/Fleet/RescueFleetController`         |

## Tests

```bash
php bin/phpunit
```

| Suite                                      | What it checks                                              |
|--------------------------------------------|-------------------------------------------------------------|
| `tests/Functional/Application/Fleet`       | Assembling a fleet for 1050 passengers                      |
| `tests/Integration/Infrastructure/Swapi`   | The SWAPI client: pagination and filtering of invalid values |
| `tests/Api/Fleet`                          | The HTTP API: creation, `Location` header, read by id       |

Tests never call SWAPI: responses are replayed from `tests/Fixtures/Http/`.
A test picks its set of responses with `#[HttpFixtures('swapi/two_pages')]`.
