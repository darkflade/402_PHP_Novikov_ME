<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'game_id',
        'played_at',
        'number',
        'user_answer',
        'is_prime',
        'is_correct',
        'divisors',
    ];

    protected $casts = [
        'played_at' => 'datetime',
        'number' => 'integer',
        'is_prime' => 'boolean',
        'is_correct' => 'boolean',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
