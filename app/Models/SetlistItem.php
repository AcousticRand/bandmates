<?php

namespace App\Models;

use Database\Factories\SetlistItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetlistItem extends Model
{
    /** @use HasFactory<SetlistItemFactory> */
    use HasFactory;

    protected $fillable = [
        'setlist_id',
        'song_id',
        'set_number',
        'sort_order',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'set_number' => 'integer',
            'sort_order' => 'integer',
            'position' => 'decimal:10',
        ];
    }

    public function setlist(): BelongsTo
    {
        return $this->belongsTo(Setlist::class);
    }

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }
}
