<?php

declare(strict_types=1);

namespace App\Tests\Functional\UI\API\Fleet;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\Fleets;
use App\Domain\Fleet\StarShip;
use App\Tests\Helper\ApiTestCase;
use Zenstruck\Browser\HttpOptions;

final class RescueFleetControllerTest extends ApiTestCase
{
    public function testShouldAssembleARescueFleet(): void
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

    public function testShouldReturnAFleetGivenAnId(): void
    {
        $browser = $this->browser();
        $fleets = $browser->client()->getContainer()->get(Fleets::class);

        $fleet = $fleets->save(
            Fleet::from(
                collect([new Starship('Millennium Falcon', 6, 100000)]),
            ),
        );

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
