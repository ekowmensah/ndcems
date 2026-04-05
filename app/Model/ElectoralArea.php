<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class ElectoralArea extends Model
{
    protected $table = "electoralarea";
    protected $fillable = [
        "name",
        "country_id",
        "region_id",
        "constituency_id"
    ];



}
