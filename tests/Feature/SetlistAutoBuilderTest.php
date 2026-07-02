<?php

use App\Enums\EnergyLevel;
use App\Filament\Resources\Setlists\Pages\SetlistBoardPage;
use App\Models\Setlist;
use App\Models\SetlistItem;
use App\Models\Song;
use App\Services\SetlistAutoBuilder;
use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('places opener songs first in each set', function () {
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 1,
        'show_length_minutes' => 60,
    ]);

    $opener = Song::factory()->create([
        'team_id' => $this->team->id,
        'is_opener' => true,
        'energy_level' => EnergyLevel::High,
        'is_acoustic' => false,
        'runtime' => 240,
    ]);

    $other = Song::factory()->create([
        'team_id' => $this->team->id,
        'is_opener' => false,
        'energy_level' => EnergyLevel::Low,
        'is_acoustic' => false,
        'runtime' => 200,
    ]);

    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $opener->id, 'set_number' => 0, 'sort_order' => 0]);
    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $other->id, 'set_number' => 0, 'sort_order' => 0]);

    app(SetlistAutoBuilder::class)->build($setlist);

    $openerItem = SetlistItem::where('setlist_id', $setlist->id)->where('song_id', $opener->id)->first();
    $otherItem = SetlistItem::where('setlist_id', $setlist->id)->where('song_id', $other->id)->first();

    expect($openerItem->set_number)->toBe(1);
    expect($otherItem->set_number)->toBe(1);
    expect($openerItem->position)->toBeLessThan($otherItem->position);
});

it('places acoustic songs in the middle of the set, not at the start', function () {
    // 90-min show, 1 set → target ≈ 5400 s. Songs are 200 s (~3:20) each.
    // avg runtime = 200 s, estimated songs = floor(5400/200) = 27, acoustic cap = floor(27*0.40) = 10.
    // Electric budget = 5400 - 10*200 = 3400 s → up to 17 electric.
    // We only provide 4 electric + 2 acoustic so all fit with room to spare.
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 1,
        'show_length_minutes' => 90,
    ]);

    $electricSongs = Song::factory()->count(4)->create([
        'team_id' => $this->team->id,
        'is_acoustic' => false,
        'is_opener' => false,
        'runtime' => 200,
    ]);

    $acousticSongs = Song::factory()->count(2)->create([
        'team_id' => $this->team->id,
        'is_acoustic' => true,
        'is_opener' => false,
        'runtime' => 200,
    ]);

    foreach ([...$electricSongs, ...$acousticSongs] as $song) {
        SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 0, 'sort_order' => 0]);
    }

    app(SetlistAutoBuilder::class)->build($setlist);

    $acousticPositions = SetlistItem::whereIn('song_id', $acousticSongs->pluck('id'))
        ->where('setlist_id', $setlist->id)
        ->pluck('position');

    $electricPositions = SetlistItem::whereIn('song_id', $electricSongs->pluck('id'))
        ->where('setlist_id', $setlist->id)
        ->pluck('position');

    // At least one electric song must appear before every acoustic song.
    expect($electricPositions->min())->toBeLessThan($acousticPositions->min());
});

it('keeps acoustic songs at or below 40% of total set songs', function () {
    // 90-min show, 1 set. Use many acoustic songs to confirm the cap holds.
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 1,
        'show_length_minutes' => 90,
    ]);

    // 6 electric + 10 acoustic — without a cap, acoustic would exceed 40%.
    Song::factory()->count(6)->create(['team_id' => $this->team->id, 'is_acoustic' => false, 'is_opener' => false, 'runtime' => 240]);
    Song::factory()->count(10)->create(['team_id' => $this->team->id, 'is_acoustic' => true, 'is_opener' => false, 'runtime' => 240]);

    $allSongs = Song::where('team_id', $this->team->id)->get();
    foreach ($allSongs as $song) {
        SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 0, 'sort_order' => 0]);
    }

    app(SetlistAutoBuilder::class)->build($setlist);

    $placed = SetlistItem::where('setlist_id', $setlist->id)->where('set_number', 1)->with('song')->get();

    $total = $placed->count();
    $acoustic = $placed->filter(fn ($item) => $item->song->is_acoustic)->count();

    expect($total)->toBeGreaterThan(0);
    expect($acoustic / $total)->toBeLessThanOrEqual(0.40);
});

it('leaves songs without runtime in the library', function () {
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 1,
        'show_length_minutes' => 60,
    ]);

    $withRuntime = Song::factory()->create(['team_id' => $this->team->id, 'is_opener' => false, 'runtime' => 200]);
    $withoutRuntime = Song::factory()->create(['team_id' => $this->team->id, 'runtime' => null]);

    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $withRuntime->id, 'set_number' => 0, 'sort_order' => 0]);
    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $withoutRuntime->id, 'set_number' => 0, 'sort_order' => 0]);

    app(SetlistAutoBuilder::class)->build($setlist);

    expect(SetlistItem::where('setlist_id', $setlist->id)->where('song_id', $withoutRuntime->id)->value('set_number'))->toBe(0);
    expect(SetlistItem::where('setlist_id', $setlist->id)->where('song_id', $withRuntime->id)->value('set_number'))->toBe(1);
});

it('distributes songs across multiple sets', function () {
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 2,
        'show_length_minutes' => 60,
    ]);

    // 60 min - 22 min break = 38 min total / 2 sets = 19 min (1140 sec) each
    // Two openers: each will anchor a set
    $opener1 = Song::factory()->create(['team_id' => $this->team->id, 'is_opener' => true, 'runtime' => 200]);
    $opener2 = Song::factory()->create(['team_id' => $this->team->id, 'is_opener' => true, 'runtime' => 200]);

    foreach ([$opener1, $opener2] as $song) {
        SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 0, 'sort_order' => 0]);
    }

    app(SetlistAutoBuilder::class)->build($setlist);

    $set1Songs = SetlistItem::where('setlist_id', $setlist->id)->where('set_number', 1)->count();
    $set2Songs = SetlistItem::where('setlist_id', $setlist->id)->where('set_number', 2)->count();

    expect($set1Songs)->toBeGreaterThanOrEqual(1);
    expect($set2Songs)->toBeGreaterThanOrEqual(1);
});

it('does not exceed target set duration', function () {
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 1,
        'show_length_minutes' => 10, // 600 seconds target
    ]);

    // Three songs that together exceed 10 minutes
    foreach ([300, 250, 250] as $runtime) {
        $song = Song::factory()->create(['team_id' => $this->team->id, 'is_opener' => false, 'runtime' => $runtime]);
        SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 0, 'sort_order' => 0]);
    }

    app(SetlistAutoBuilder::class)->build($setlist);

    $setItems = SetlistItem::where('setlist_id', $setlist->id)
        ->where('set_number', 1)
        ->with('song')
        ->get();

    $totalRuntime = $setItems->sum(fn ($item) => $item->song->getRawOriginal('runtime'));

    expect($totalRuntime)->toBeLessThanOrEqual(600);
});

it('never assigns the same song to more than one set across three sets', function () {
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 3,
        'show_length_minutes' => 90,
    ]);

    // 90 min - 30 min break = 60 min / 3 = 20 min (1200 sec) each
    // Create enough songs to fill all three sets
    $songs = collect();
    foreach (range(1, 9) as $i) {
        $song = Song::factory()->create([
            'team_id' => $this->team->id,
            'is_opener' => $i <= 3,
            'is_acoustic' => $i % 2 === 0,
            'runtime' => 200,
        ]);
        $songs->push($song);
        SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 0, 'sort_order' => 0]);
    }

    app(SetlistAutoBuilder::class)->build($setlist);

    $placedItems = SetlistItem::where('setlist_id', $setlist->id)
        ->where('set_number', '>', 0)
        ->get();

    $uniqueSongIds = $placedItems->pluck('song_id')->unique();

    // Every placed song should appear exactly once
    expect($placedItems->count())->toBe($uniqueSongIds->count());
});

it('auto-build action updates setlist and builds songs', function () {
    $setlist = Setlist::factory()->create([
        'team_id' => $this->team->id,
        'number_of_sets' => 1,
        'show_length_minutes' => 60,
    ]);

    $song = Song::factory()->create(['team_id' => $this->team->id, 'is_opener' => false, 'is_acoustic' => false, 'runtime' => 200]);
    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 0, 'sort_order' => 0]);

    Livewire::test(SetlistBoardPage::class, ['record' => $setlist->getRouteKey()])
        ->callAction('autoBuild', data: [
            'show_length_minutes' => 90,
            'number_of_sets' => 1,
        ])
        ->assertHasNoActionErrors();

    expect($setlist->fresh()->show_length_minutes)->toBe(90);
    expect(SetlistItem::where('setlist_id', $setlist->id)->where('set_number', 1)->exists())->toBeTrue();
});
