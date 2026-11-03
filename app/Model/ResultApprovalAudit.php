<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ResultApprovalAudit extends Model
{
    protected $fillable = [
        'election_result_id',
        'actor_id',
        'actor_role',
        'constituency_id',
        'action',
        'from_status',
        'to_status',
        'reason',
        'result_snapshot',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'result_snapshot' => 'array',
    ];
}
