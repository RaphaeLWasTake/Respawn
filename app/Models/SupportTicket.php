<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicket extends Model
{
    protected $fillable = ['subject', 'message', 'reply', 'status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
