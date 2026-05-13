<?php

namespace App\Models;

use App\Enums\GigStatus;
use Database\Factories\GigFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaravelDaily\FilaTeams\Models\Team;

class Gig extends Model
{
    /** @use HasFactory<GigFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'venue_id',
        'setlist_id',
        'name',
        'date',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date'   => 'date',
            'status' => GigStatus::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function setlist(): BelongsTo
    {
        return $this->belongsTo(Setlist::class);
    }
}
