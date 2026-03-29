<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'player_name',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(Step::class);
    }
}
