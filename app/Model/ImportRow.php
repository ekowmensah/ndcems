<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ImportRow extends Model
{
    protected $fillable = ['import_batch_id', 'row_number', 'payload', 'errors', 'status', 'target_id'];
    protected $casts = ['payload' => 'array', 'errors' => 'array'];
}
