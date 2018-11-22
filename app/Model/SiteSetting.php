<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class SiteSetting extends Model
{
    protected $table = "site_settings";
    protected $fillable = [
        "key",
        "value"
    ];



}

