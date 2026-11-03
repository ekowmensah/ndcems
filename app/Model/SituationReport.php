<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class SituationReport extends Model
{
    protected $fillable = ['scope_type', 'scope_id', 'generated_by', 'period_ending', 'snapshot', 'checksum'];

    protected $casts = ['period_ending' => 'datetime', 'snapshot' => 'array'];
}
