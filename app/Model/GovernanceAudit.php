<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GovernanceAudit extends Model
{
    protected $fillable = [
        'actor_guard',
        'actor_id',
        'actor_name',
        'event',
        'route',
        'method',
        'path',
        'response_status',
        'request_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'request_data' => 'array',
    ];
}
