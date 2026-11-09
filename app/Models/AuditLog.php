<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'audit';

    protected $fillable = [
        'actor_user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    public $timestamps = false;

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];
}
