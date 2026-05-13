<?php

use App\Enums\BandRole;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelDaily\FilaTeams\Models\Membership;
use LaravelDaily\FilaTeams\Models\Team;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/**
 * Create a user and band, wire them together, and set the Filament tenant context.
 *
 * @return array{user: User, team: Team}
 */
function createBandWithOwner(): array
{
    $user = User::factory()->create();
    $team = Team::create(['name' => fake()->words(2, true)]);

    Membership::create([
        'team_id' => $team->id,
        'user_id' => $user->id,
        'role'    => BandRole::Owner,
    ]);

    $user->update(['current_team_id' => $team->id]);

    return ['user' => $user, 'team' => $team];
}

/**
 * Act as the given user with the given team as the active Filament tenant.
 */
function actingAsBandOwner(User $user, Team $team): void
{
    test()->actingAs($user);

    Filament::setTenant($team);
    Filament::setCurrentPanel('admin');
    Filament::bootCurrentPanel();
}
