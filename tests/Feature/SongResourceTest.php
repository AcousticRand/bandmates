<?php

use App\Filament\Resources\Songs\Pages\CreateSong;
use App\Filament\Resources\Songs\Pages\EditSong;
use App\Filament\Resources\Songs\Pages\ListSongs;
use App\Models\Song;
use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('can list songs', function () {
    Song::factory()->count(3)->create(['team_id' => $this->team->id]);

    Livewire::test(ListSongs::class)
        ->assertOk();
});

it('can load the create song page', function () {
    Livewire::test(CreateSong::class)
        ->assertOk();
});

it('can create a song', function () {
    $data = Song::factory()->make();

    Livewire::test(CreateSong::class)
        ->fillForm([
            'title' => $data->title,
            'artist' => $data->artist,
            'album' => $data->album,
            'genre' => $data->genre,
        ])
        ->call('create')
        ->assertNotified()
        ->assertRedirect();

    $song = Song::where('title', $data->title)->where('team_id', $this->team->id)->first();
    expect($song)->not->toBeNull()
        ->and($song->album)->toBe($data->album);
});

it('requires title when creating a song', function () {
    Livewire::test(CreateSong::class)
        ->fillForm(['title' => ''])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

it('can load the edit song page', function () {
    $song = Song::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(EditSong::class, ['record' => $song->id])
        ->assertOk()
        ->assertSchemaStateSet(['title' => $song->title]);
});

it('rejects an invalid runtime format', function () {
    Livewire::test(CreateSong::class)
        ->fillForm(['title' => 'Test Song', 'runtime' => '4:75'])
        ->call('create')
        ->assertHasFormErrors(['runtime' => 'regex']);
});

it('accepts a valid mm:ss runtime', function () {
    Livewire::test(CreateSong::class)
        ->fillForm(['title' => 'Test Song', 'runtime' => '4:30'])
        ->call('create')
        ->assertHasNoFormErrors();

    $song = Song::where('title', 'Test Song')->where('team_id', $this->team->id)->first();
    expect($song)->not->toBeNull()
        ->and($song->runtime)->toBe('4:30')
        ->and($song->getAttributes()['runtime'])->toBe(270);
});

it('defaults a new song to active', function () {
    $data = Song::factory()->make();

    Livewire::test(CreateSong::class)
        ->fillForm(['title' => $data->title])
        ->call('create')
        ->assertNotified();

    $song = Song::where('title', $data->title)->where('team_id', $this->team->id)->first();
    expect($song->is_active)->toBeTrue();
});

it('can update a song', function () {
    $song = Song::factory()->create(['team_id' => $this->team->id]);
    $newData = Song::factory()->make();

    Livewire::test(EditSong::class, ['record' => $song->id])
        ->fillForm(['title' => $newData->title])
        ->call('save')
        ->assertNotified();

    expect($song->fresh()->title)->toBe($newData->title);
});
