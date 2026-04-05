<?php
namespace App\Page;
use App\User;
use BadMethodCallException;
use Services_Twilio;
use stdClass;
use Illuminate\Support\Facades\Hash;
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

        $user =  User::select(
            'pollingstation.name as PollingStation_name',
            'users.name as user_name',
            'users.*'
            )
        ->where('username',$this->username)
        ->where('user_type_id',self::POLLING_AGENT)
        ->join('pollingstation','pollingstation.id','=','users.polling_station_id')
        ->first();
        if(!$user || !$user->secret){
            return false;
        }

        $secretMatchesHash = Hash::check($this->password, $user->secret);
        $secretMatchesLegacyPlain = hash_equals((string) $user->secret, (string) $this->password);
        if(!$secretMatchesHash && !$secretMatchesLegacyPlain){
            return false;
        }

        // Seamless migration path: convert legacy plaintext secret to hash on next successful login.
        if($secretMatchesLegacyPlain && !$secretMatchesHash){
            $user->secret = Hash::make($this->password);
            $user->save();
        }

        return $user;
    }
}

