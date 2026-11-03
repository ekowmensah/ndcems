<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    protected $fillable = ['type', 'original_filename', 'status', 'uploaded_by', 'total_rows', 'valid_rows', 'invalid_rows', 'summary', 'committed_at', 'rolled_back_at'];
    protected $casts = ['summary' => 'array', 'committed_at' => 'datetime', 'rolled_back_at' => 'datetime'];

    public function rows()
    {
        return $this->hasMany(ImportRow::class);
    }
}
