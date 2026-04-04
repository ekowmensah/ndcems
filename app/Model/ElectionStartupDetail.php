<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class ElectionStartupDetail extends Model
{
    protected $table = "election_startup_detail";
    protected $fillable = [
        "election_name",
        "start",
        "end",
        "total_constituency",
        "total_electral",
        "total_polling",
        "total_voters",
        "status",
        "election_type_id",
    ];



}

