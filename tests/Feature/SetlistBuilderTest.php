<?php

use App\Filament\Resources\Setlists\Pages\SetlistBoardPage;
use App\Models\Setlist;
use App\Models\SetlistItem;
use App\Models\Song;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('syncs all team songs into the library on mount', function () {
    $setlist = Setlist::factory()->create(['team_id' => $this->team->id, 'number_of_sets' => 1]);
    $song1 = Song::factory()->create(['team_id' => $this->team->id]);
    $song2 = Song::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(SetlistBoardPage::class, ['record' => $setlist->getRouteKey()])
        ->assertSuccessful();

    expect(SetlistItem::where('setlist_id', $setlist->id)->count())->toBe(2);
    expect(SetlistItem::where(['setlist_id' => $setlist->id, 'song_id' => $song1->id, 'set_number' => 0])->exists())->toBeTrue();
    expect(SetlistItem::where(['setlist_id' => $setlist->id, 'song_id' => $song2->id, 'set_number' => 0])->exists())->toBeTrue();
});

it('does not move songs already in a set back to the library', function () {
    $setlist = Setlist::factory()->create(['team_id' => $this->team->id, 'number_of_sets' => 1]);
    $song = Song::factory()->create(['team_id' => $this->team->id]);

    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 1, 'sort_order' => 1]);

    Livewire::test(SetlistBoardPage::class, ['record' => $setlist->getRouteKey()])
        ->assertSuccessful();

    expect(SetlistItem::where('setlist_id', $setlist->id)->count())->toBe(1);
    expect(SetlistItem::where(['setlist_id' => $setlist->id, 'song_id' => $song->id, 'set_number' => 1])->exists())->toBeTrue();
});

it('only adds songs not yet in the setlist to the library', function () {
    $setlist = Setlist::factory()->create(['team_id' => $this->team->id, 'number_of_sets' => 2]);
    $inSet = Song::factory()->create(['team_id' => $this->team->id]);
    $inLibrary = Song::factory()->create(['team_id' => $this->team->id]);
    $newSong = Song::factory()->create(['team_id' => $this->team->id]);

    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $inSet->id, 'set_number' => 1, 'sort_order' => 1]);
    SetlistItem::create(['setlist_id' => $setlist->id, 'song_id' => $inLibrary->id, 'set_number' => 0, 'sort_order' => 0]);

    Livewire::test(SetlistBoardPage::class, ['record' => $setlist->getRouteKey()])
        ->assertSuccessful();

    expect(SetlistItem::where('setlist_id', $setlist->id)->count())->toBe(3);
    expect(SetlistItem::where(['setlist_id' => $setlist->id, 'song_id' => $newSong->id, 'set_number' => 0])->exists())->toBeTrue();
    expect(SetlistItem::where(['setlist_id' => $setlist->id, 'song_id' => $inSet->id, 'set_number' => 1])->exists())->toBeTrue();
});

it('does not include songs from other teams in the library', function () {
    ['team' => $otherTeam] = createBandWithOwner();

    // Bypass Filament's BelongsToTenant creating hook, which forces team_id = current tenant.
    DB::table('songs')->insert(['team_id' => $otherTeam->id, 'title' => 'Other Team Song', 'created_at' => now(), 'updated_at' => now()]);

    $setlist = Setlist::factory()->create(['team_id' => $this->team->id, 'number_of_sets' => 1]);
    $ownSong = Song::factory()->create(['team_id' => $this->team->id]);

    Livewire::test(SetlistBoardPage::class, ['record' => $setlist->getRouteKey()])
        ->assertSuccessful();

    expect(SetlistItem::where('setlist_id', $setlist->id)->count())->toBe(1);
    expect(SetlistItem::where(['setlist_id' => $setlist->id, 'song_id' => $ownSong->id])->exists())->toBeTrue();
});
