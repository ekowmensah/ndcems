<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class PollingStation extends Model
{
    protected $table = "PollingStation";
    protected $fillable = [
        "name",
        "country_id",
        "region_id",
        "constituency_id","polling_station_id","electoralarea_id"
    ];



}

