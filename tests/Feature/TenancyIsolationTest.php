<?php

use App\Models\Gig;
use App\Models\Setlist;
use App\Models\Song;
use App\Models\Venue;

it('songs are scoped to the current band', function () {
    ['user' => $userA, 'team' => $teamA] = createBandWithOwner();
    ['team' => $teamB] = createBandWithOwner();

    $songA = Song::factory()->create(['team_id' => $teamA->id]);
    $songB = Song::factory()->create(['team_id' => $teamB->id]);

    actingAsBandOwner($userA, $teamA);

    expect(Song::all()->pluck('id'))
        ->toContain($songA->id)
        ->not->toContain($songB->id);
});

it('setlists are scoped to the current band', function () {
    ['user' => $userA, 'team' => $teamA] = createBandWithOwner();
    ['team' => $teamB] = createBandWithOwner();

    $setlistA = Setlist::factory()->create(['team_id' => $teamA->id]);
    $setlistB = Setlist::factory()->create(['team_id' => $teamB->id]);

    actingAsBandOwner($userA, $teamA);

    expect(Setlist::all()->pluck('id'))
        ->toContain($setlistA->id)
        ->not->toContain($setlistB->id);
});

it('venues are scoped to the current band', function () {
    ['user' => $userA, 'team' => $teamA] = createBandWithOwner();
    ['team' => $teamB] = createBandWithOwner();

    $venueA = Venue::factory()->create(['team_id' => $teamA->id]);
    $venueB = Venue::factory()->create(['team_id' => $teamB->id]);

    actingAsBandOwner($userA, $teamA);

    expect(Venue::all()->pluck('id'))
        ->toContain($venueA->id)
        ->not->toContain($venueB->id);
});

it('gigs are scoped to the current band', function () {
    ['user' => $userA, 'team' => $teamA] = createBandWithOwner();
    ['team' => $teamB] = createBandWithOwner();

    $gigA = Gig::factory()->create(['team_id' => $teamA->id]);
    $gigB = Gig::factory()->create(['team_id' => $teamB->id]);

    actingAsBandOwner($userA, $teamA);

    expect(Gig::all()->pluck('id'))
        ->toContain($gigA->id)
        ->not->toContain($gigB->id);
});
