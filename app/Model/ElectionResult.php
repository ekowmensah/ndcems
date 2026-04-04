<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class ElectionResult extends Model
{
    protected $table = "election_result";
    protected $fillable = [
        "polling_station_id",
        "user_id",

        "user_type_id",
        "country_id",
        "region_id",
        "constituency_id",
        "electoral_area_id",
        "election_type_id",


        "obtained_votes",
        "total_ballot",
        "total_rejected_ballot",
        "election_start_up_id",
        "verify_by_regional",
        "verify_by_constituency",
        "result_by_constituency"
    ];
}

