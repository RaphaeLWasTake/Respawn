<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePackage extends Model
{
    protected $fillable = [
        'name',
        'amount',
        'price',
        'stock',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
