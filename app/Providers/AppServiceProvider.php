<?php

namespace App\Providers;

use App\Models\Gig;
use App\Models\Song;
use App\Models\Setlist;
use App\Models\Venue;
use Illuminate\Support\ServiceProvider;
use LaravelDaily\FilaTeams\Models\Team;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Team::resolveRelationUsing('songs', fn (Team $team) => $team->hasMany(Song::class));
        Team::resolveRelationUsing('setlists', fn (Team $team) => $team->hasMany(Setlist::class));
        Team::resolveRelationUsing('venues', fn (Team $team) => $team->hasMany(Venue::class));
        Team::resolveRelationUsing('gigs', fn (Team $team) => $team->hasMany(Gig::class));
    }
}
