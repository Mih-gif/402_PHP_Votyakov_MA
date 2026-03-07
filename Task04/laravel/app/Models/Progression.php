<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Progression extends Model
{
    protected $fillable = [
        'game_id', 'first_number', 'step', 
        'missing_position', 'correct_number', 'user_answer'
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function numbers(): HasMany
    {
        return $this->hasMany(ProgressionNumber::class);
    }
}