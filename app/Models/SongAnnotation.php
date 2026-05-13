<?php

namespace App\Models;

use Database\Factories\SongAnnotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongAnnotation extends Model
{
    /** @use HasFactory<SongAnnotationFactory> */
    use HasFactory;

    protected $fillable = [
        'song_id',
        'user_id',
        'annotation',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
