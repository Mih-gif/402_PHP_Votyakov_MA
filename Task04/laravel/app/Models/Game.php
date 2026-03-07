<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Game extends Model
{
    protected $fillable = ['player_name', 'is_finished', 'result'];

    protected $casts = [
        'is_finished' => 'boolean',
    ];

    public function progression(): HasOne
    {
        return $this->hasOne(Progression::class);
    }

    public function progressionNumbers(): HasManyThrough
    {
        return $this->through('progression')->has('numbers');
    }
}