<?php

namespace App\Models;

use Database\Factories\SetlistFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LaravelDaily\FilaTeams\Models\Team;

class Setlist extends Model
{
    /** @use HasFactory<SetlistFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'description',
        'number_of_sets',
    ];

    protected function casts(): array
    {
        return [
            'number_of_sets' => 'integer',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SetlistItem::class);
    }

    public function gigs(): HasMany
    {
        return $this->hasMany(Gig::class);
    }
}
