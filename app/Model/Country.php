<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class Country extends Model
{
    protected $table = "countries";
    protected $fillable = [
        "name",
        "country_id"
    ];



}

