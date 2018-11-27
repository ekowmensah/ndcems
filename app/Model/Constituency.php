<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class Constituency extends Model
{
    protected $table = "constituency";
    protected $fillable = [
        "name",
        "country_id",
        "region_id",
        "total_candidates"
    ];



}

