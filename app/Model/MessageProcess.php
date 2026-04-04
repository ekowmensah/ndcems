<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class MessageProcess extends Model
{
    //
	protected $table = 'message_process';
	protected $fillable = [
        'type', 'stage','object','phone','obj','user_id','data'
    ];
	 protected $casts = [
        'type' => 'integer',
		'stage'=>'integer'
    ];
}
