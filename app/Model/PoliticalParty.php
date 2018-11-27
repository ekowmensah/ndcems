<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class PoliticalParty extends Model
{
    protected $table = "political_party";
    protected $fillable = [
        "name",
        "logo",
        "party_id",
        "party_initial"
    ];



}

