<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class ElectionType extends Model
{
    protected $table = "election_type";
    protected $fillable = [
        "name"
    ];



}

