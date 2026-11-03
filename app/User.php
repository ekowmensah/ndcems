<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    public const TYPE_NATIONAL_DIRECTOR = 1;
    public const TYPE_REGIONAL_DIRECTOR = 2;
    public const TYPE_CONSTITUENCY_DIRECTOR = 3;
    public const TYPE_POLLING_AGENT = 5;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','username','user_type_id','gender','constituency','phoneno',
        "country_id",
        "region_id",
        "constituency_id","polling_station_id","electoralarea_id","added_by","secret","photo"
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];

    public function canCaptureResults(): bool
    {
        return in_array((int) $this->user_type_id, [self::TYPE_CONSTITUENCY_DIRECTOR, self::TYPE_POLLING_AGENT], true);
    }

    public function canApproveResults(): bool
    {
        return (int) $this->user_type_id === self::TYPE_CONSTITUENCY_DIRECTOR;
    }

    public function canReviewRegionalResults(): bool
    {
        return (int) $this->user_type_id === self::TYPE_REGIONAL_DIRECTOR;
    }

    public function canReviewNationalResults(): bool
    {
        return (int) $this->user_type_id === self::TYPE_NATIONAL_DIRECTOR;
    }
}
