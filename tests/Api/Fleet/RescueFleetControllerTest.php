<?php

declare(strict_types=1);

namespace App\Tests\Api\Fleet;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\Fleets;
use App\Domain\Fleet\StarShip;
use App\Tests\Support\ApiTestCase;
use App\Tests\Support\Http\HttpFixtures;
use PHPUnit\Framework\Attributes\Test;
use Zenstruck\Browser\HttpOptions;

final class RescueFleetControllerTest extends ApiTestCase
{
    #[Test]
    #[HttpFixtures('swapi/starships')]
    public function should_assemble_a_rescue_fleet(): void
    {
        $this
            ->browser()
            ->post('rescueFleets', HttpOptions::json(['numberOfPassengers' => 5]))
            ->assertStatus(201)
            ->json()
            ->assertHas('id')
            ->assertMatches('length(starships)', 1)
            ->assertMatches('starships[0].name', 'Millennium Falcon')
            ->assertMatches('starships[0].capacity', 6)
        ;
    }

    #[Test]
    public function should_return_a_fleet_given_an_id(): void
    {
        $browser = $this->browser();
        $fleets = $browser->client()->getContainer()->get(Fleets::class);

        $fleet = [new Starship('Millennium Falcon', 6, 100000)]
                |> collect(...)
                |> Fleet::from(...)
                |> $fleets->save(...);

        $starship = $fleet->starships->first();

        $browser
            ->get(sprintf('rescueFleets/%s', $fleet->id))
            ->assertSuccessful()
            ->json()
            ->assertHas('id')
            ->assertMatches('length(starships)', $fleet->starships->count())
            ->assertMatches('starships[0].name', $starship->name)
            ->assertMatches('starships[0].capacity', $starship->capacity)
        ;
    }
}
