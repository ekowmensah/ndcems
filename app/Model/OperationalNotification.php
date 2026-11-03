<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OperationalNotification extends Model
{
    protected $fillable = ['recipient_user_id', 'type', 'severity', 'title', 'message', 'action_url', 'source_type', 'source_id', 'fingerprint', 'read_at', 'dismissed_at'];

    protected $casts = ['read_at' => 'datetime', 'dismissed_at' => 'datetime'];
}
