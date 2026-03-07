<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressionNumber extends Model
{
    protected $fillable = ['progression_id', 'position', 'number', 'is_missing'];

    protected $casts = [
        'is_missing' => 'boolean',
    ];

    public function progression(): BelongsTo
    {
        return $this->belongsTo(Progression::class);
    }
}