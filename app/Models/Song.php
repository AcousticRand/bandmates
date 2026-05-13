<?php

namespace App\Models;

use Database\Factories\SongFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LaravelDaily\FilaTeams\Models\Team;

class Song extends Model
{
    /** @use HasFactory<SongFactory> */
    use HasFactory;

    protected $fillable = [
        'team_id',
        'title',
        'artist',
        'album',
        'genre',
        'release_year',
        'lyrics',
        'notes',
        'tempo',
        'key',
        'arrangement',
        'runtime',
        'has_track',
        'is_acoustic'
    ];

    protected function casts(): array
    {
        return [
            'release_year' => 'integer',
        ];
    }

    /**
     * Stores runtime as seconds in the DB; exposes and accepts mm:ss strings externally.
     */
    protected function runtime(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value !== null
                ? sprintf('%d:%02d', intdiv($value, 60), $value % 60)
                : null,
            set: function (?string $value): ?int {
                if (blank($value)) {
                    return null;
                }
                if (str_contains($value, ':')) {
                    [$minutes, $seconds] = explode(':', $value, 2);

                    return (int) $minutes * 60 + (int) $seconds;
                }

                return (int) $value;
            },
        );
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(SongAnnotation::class);
    }

    public function setlistItems(): HasMany
    {
        return $this->hasMany(SetlistItem::class);
    }
}
