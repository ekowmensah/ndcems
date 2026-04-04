<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class Candidate extends Model
{
    protected $table = "candidates";
    protected $fillable = [
        "first_name",
        "last_name",
        "photo",
        "dob",
        "election_id",
        "personal",
        "party_id",
        "region_id",
        "constituency_id",
        "polling_station_id",
        "is_disabled",
        "id_no",
        "phone",
        "electoral_area_id",
        "election_start_up_id",
        "ordering_position"
    ];



}

