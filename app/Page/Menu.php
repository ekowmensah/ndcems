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
use App\Model\ElectionStartupDetail;


class Menu implements Constant {
    public $body;
    public function __construct($body){
        $this->body = $body;
    }
    public function menu(){
        $body = $this->body;
        $message ="Reply with number for which election. \n\n";
        if($body==1 || $body == 2)
        {
            $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
                )
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("status",1)->get();
            foreach ($electionStartupDetail as $key => $election) {
                $message.=$election->id ." : ". $election->election_name. "\n\n";
            }
            return array('body'=>$body,'election'=>$message);

        }else{
            return false;

        }
    }


}
