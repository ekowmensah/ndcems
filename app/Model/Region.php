<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class Region extends Model
{
    protected $table = "region";
    protected $fillable = [
        "name",
        "country_id"
    ];



}

