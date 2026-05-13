<?php

use App\Filament\Resources\Setlists\Pages\CreateSetlist;
use App\Filament\Resources\Setlists\Pages\EditSetlist;
use App\Filament\Resources\Setlists\Pages\ListSetlists;
use App\Models\Setlist;

use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('can list setlists', function () {
    Setlist::factory()->count(2)->create(['team_id' => $this->team->id]);

    Livewire::test(ListSetlists::class)
        ->assertOk();
});

it('can load the create setlist page', function () {
    Livewire::test(CreateSetlist::class)
        ->assertOk();
});

it('can create a setlist', function () {
    Livewire::test(CreateSetlist::class)
        ->fillForm([
            'name'           => 'Summer Tour Set',
            'number_of_sets' => 2,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    expect(Setlist::where('name', 'Summer Tour Set')->where('team_id', $this->team->id)->exists())->toBeTrue();
});

it('requires name when creating a setlist', function () {
    Livewire::test(CreateSetlist::class)
        ->fillForm(['name' => ''])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can load the edit setlist page', function () {
    $setlist = Setlist::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(EditSetlist::class, ['record' => $setlist->id])
        ->assertOk()
        ->assertSchemaStateSet(['name' => $setlist->name]);
});
