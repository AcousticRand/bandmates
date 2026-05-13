<?php

use App\Enums\GigStatus;
use App\Filament\Resources\Gigs\Pages\CreateGig;
use App\Filament\Resources\Gigs\Pages\EditGig;
use App\Filament\Resources\Gigs\Pages\ListGigs;
use App\Models\Gig;

use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('can list gigs', function () {
    Gig::factory()->count(2)->create(['team_id' => $this->team->id]);

    Livewire::test(ListGigs::class)
        ->assertOk();
});

it('can load the create gig page', function () {
    Livewire::test(CreateGig::class)
        ->assertOk();
});

it('can create a gig', function () {
    Livewire::test(CreateGig::class)
        ->fillForm([
            'name'   => 'Summer Festival',
            'date'   => '2026-07-04',
            'status' => GigStatus::Upcoming,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    expect(Gig::where('name', 'Summer Festival')->where('team_id', $this->team->id)->exists())->toBeTrue();
});

it('requires name and date when creating a gig', function (array $data, array $errors) {
    Livewire::test(CreateGig::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors);
})->with([
    'name is required' => [['name' => ''], ['name' => 'required']],
    'date is required' => [['name' => 'Test Gig', 'date' => null], ['date' => 'required']],
]);

it('can load the edit gig page', function () {
    $gig = Gig::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(EditGig::class, ['record' => $gig->id])
        ->assertOk()
        ->assertSchemaStateSet(['name' => $gig->name]);
});
