<?php
namespace App\Page;
use App\User;
use BadMethodCallException;
use Services_Twilio;
use stdClass;
use Hash;
use App\UserOption;
use App\Referrer;
use App\Page\Constant;
use App\Page\Messages;

class Login implements Constant {
    public $from,$username,$password;
    public function __construct($login_detail){
        $detail = explode(',', $login_detail);
        if(!isset($detail[1])){

        }else{
            $this->username = $detail[0];
            $this->password = $detail[1];
        }

    }
    public function login(){

        $username =  User::select(
            'PollingStation.name as PollingStation_name',
            'users.name as user_name',
            'users.*'
            )
        ->where('username',$this->username)
        ->where('user_type_id',self::POLLING_AGENT)
        ->where('secret',$this->password)
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')
        ->first();
        return $username;
    }
}
