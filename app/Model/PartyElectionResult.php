<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class PartyElectionResult extends Model
{
    protected $table = "party_election_result";
    protected $fillable = [
        "election_result_id",
        "user_id",
        "polling_station_id",
        "party_id",
        "obtained_vote",
        "candidate_id",
        "result_by_constituency",
        "country_id",
        "region_id",
        "constituency_id",
        "electoral_area_id"
    ];
}

