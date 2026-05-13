<?php

use App\Filament\Resources\Venues\Pages\CreateVenue;
use App\Filament\Resources\Venues\Pages\EditVenue;
use App\Filament\Resources\Venues\Pages\ListVenues;
use App\Models\Venue;

use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('can list venues', function () {
    Venue::factory()->count(2)->create(['team_id' => $this->team->id]);

    Livewire::test(ListVenues::class)
        ->assertOk();
});

it('can load the create venue page', function () {
    Livewire::test(CreateVenue::class)
        ->assertOk();
});

it('can create a venue', function () {
    Livewire::test(CreateVenue::class)
        ->fillForm(['name' => 'The Fillmore', 'city' => 'San Francisco', 'state' => 'CA'])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    expect(Venue::where('name', 'The Fillmore')->where('team_id', $this->team->id)->exists())->toBeTrue();
});

it('requires name when creating a venue', function () {
    Livewire::test(CreateVenue::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can load the edit venue page', function () {
    $venue = Venue::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(EditVenue::class, ['record' => $venue->id])
        ->assertOk()
        ->assertSchemaStateSet(['name' => $venue->name]);
});
