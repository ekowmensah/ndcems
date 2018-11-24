<?php

namespace App\Model;
use Illuminate\Database\Eloquent\Model;
use Auth;


class UserType extends Model
{
    protected $table = "user_type";
    protected $fillable = [
        "name",
        "parent"
    ];



}

