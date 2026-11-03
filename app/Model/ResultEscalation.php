<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ResultEscalation extends Model
{
    protected $fillable = [
        'election_result_id',
        'created_by',
        'created_by_role',
        'category',
        'severity',
        'note',
        'status',
        'assigned_to',
        'acknowledged_by',
        'acknowledged_at',
        'due_at',
        'last_activity_at',
        'resolved_by',
        'resolved_at',
        'resolution_note',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'due_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];
}
