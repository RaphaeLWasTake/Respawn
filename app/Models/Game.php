<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'genre',
        'icon',
        'image',
        'tag',
        'color',
        'currency',
        'id_label',
        'region',
        'active',
    ];

    protected $casts = [
        'region' => 'boolean',
        'active' => 'boolean',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(GamePackage::class);
    }
}
