<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Model\MessageProcess;
use Illuminate\Support\Facades\Hash;
use App\Page\Constant;
use App\Page\Messages;
use App\Page\Candidate;
use App\Page\Login;
use App\Page\Menu;
use Twilio\Rest\Client;
use Exception;

class MessageHandler extends BaseController implements Constant,Messages
{
        private $request;
        public $from,$username,$password,$phone,$obj,$message,$response_text,$message_process,$user_id,$previour_stage_data = '',$logout=false,
        $user = false,$state,$body,$lcBody,$link,$opt,$main;

        public function handleNew(Request $request){
            try{
                $req = $request->all();
                $this->from =  $request->From;
                if(!$this->from) return;

                $this->message = $request->Body;
                $this->state = $this->getType();

            }catch(Exception $e){
                $this->response_text = $e->getMessage();
                $this->logout();
                $this->from = "+923457286380";
            }
            //$this->sendMessage($this->from,$this->response_text);
            $array = array(
                'body'=>$this->response_text,
                'From'=>$request->From
            );
                return json_encode($array);
        }

        public function handle(Request $request){
            try{
                $req = $request->all();
                $this->from =  $request->From;
                if(!$this->from) return;

                $this->message = $request->Body;
                $this->state = $this->getType();

            }catch(Exception $e){
                $this->response_text = $e->getMessage();
                $this->logout();
                $this->from = "+923457286380";
            }
            $this->sendMessage($this->from,$this->response_text);

                return $this->response_text;
        }
        public function getType(){
            $type = MessageProcess::where("phone",$this->from)->orderBy('id','desc')->first();
            //$type = $this->init($this->from,self::POLLING_AGENT,self::STATUS_WELCOME);
            if( $this->message == "0"){
                $type->delete();
                $type = MessageProcess::where("phone",$this->from)->orderBy('id','desc')->first();
                if($type){
                    $this->response_text = json_decode($type->obj)->sent;

                    return $type->stage;
                }
            }
            if(!$type){
                $this->response_text = self::WELCOME_MESSAGE;
                $obj = array("received"=>$this->message,"sent"=>$this->response_text);
                $type = $this->init($this->from,self::POLLING_AGENT,self::STATUS_LOGIN,$obj);
                $stage = self::STATUS_LOGIN;
            }
            else {
                $stage = $type->stage;
                $this->user_id = @$type->user_id;
                if(!$type->data){
                    $this->previour_stage_data = '';
                }else{
                    $this->previour_stage_data =@$type->data;
                }
                $this->message_process = $type;
                $this->response_text = $this->setMessage($stage);
            }

            return $stage;
        }

        public function init($from,$type,$stage = 0,$obj){

            return MessageProcess::create(["type"=>$type,"phone"=>$from,"stage"=>$stage,"obj"=>json_encode($obj),'user_id'=>@$this->user_id,"data"=>@$this->previour_stage_data]);
        }

        public function sendMessage($to,$message){

            $account_sid = self::TWILIO_ACCOUNT_SID;
            $auth_token = self::TWILIO_AUTH_TOKEN;
            $twilio_number = self::TWILIO_NUMBER;
            $client = new Client($account_sid, $auth_token);
            $client->messages->create(
                $to,
                array(
                    'from' => $twilio_number,
                    'body' => $message
                )
            );
            return true;
        }
        public function setMessage($stage){
            switch ($stage) {
                case self::STATUS_LOGIN:
                    $login_detail = $this->message;
                    $login = new Login($login_detail);
                    $check = $login->login();
                    if($check){
                        $this->user_id = $check->id;

                        $this->response_text = "Name : ".$check->user_name.".\nPS Name : ".$check->PollingStation_name."\n".self::MENU;
                        $this->stage = self::STATUS_MENU;
                    }else{
                        $this->response_text = self::FAIL_LOGIN;
                        $this->stage = self::STATUS_LOGIN;
                    }
                break;
                case self::STATUS_MENU:
                    $body = $this->message;
                    $menu = new Menu($body);
                    $election = $menu->menu();
                    if(!$election){
                        $this->response_text = self::MENU;
                        $this->stage = self::STATUS_MENU;
                    }else{
                        if($election['body']==1){
                            $this->response_text = $election['election'];
                            $this->stage = self::STATUS_RESULT_UPDATE;
                        }
                        else if($election['body']==2){
                            $this->response_text = $election['election'];
                            $this->stage = self::STATUS_DETAIL_ELECTION_RESULT;
                        }else{
                            $this->response_text = self::MENU;
                            $this->stage = self::STATUS_MENU;
                        }
                    }
                break;
                case self::STATUS_RESULT_UPDATE:
                    $body = $this->message;
                    $candidate = new Candidate($body);
                    $message = $candidate->candidate($this->user_id);
                    $this->previour_stage_data = json_encode(array('election_start_up_id'=>@$body),JSON_UNESCAPED_SLASHES);
                    if(!$message){
                        $this->response_text = self::MENU;
                        $this->stage = self::STATUS_MENU;
                    }else{
                        $this->response_text = $message;
                        $this->stage = self::STATUS_RESULT_UPDATE_SUBMIT;
                    }
                break;
                case self::STATUS_RESULT_UPDATE_SUBMIT:
                    $body = $this->message;
                    $candidate = new Candidate($body);
                    $message = $candidate->updateResult($this->user_id,$this->previour_stage_data);
                    if(!$message){
                        $this->response_text = self::MENU;
                        $this->stage = self::STATUS_MENU;
                    }else if($message=="verified"){
                        $this->response_text = self::RESULT_IS_VERIFIED;
                        $this->stage = self::STATUS_MENU;
                    }else{
                        $this->response_text = $message;
                        $this->stage = self::LOGOUT;
                    }
                break;

                case self::STATUS_DETAIL:
                    $body = $this->message;
                    $menu = new Menu($body);
                    $election = $menu->menu();
                    $this->response_text = $election['election'];
                    $this->stage = self::STATUS_DETAIL_ELECTION_RESULT;
                break;
                case self::STATUS_DETAIL_ELECTION_RESULT:
                    $body = $this->message;
                    $this->previour_stage_data = json_encode(array('election_start_up_id'=>@$body),JSON_UNESCAPED_SLASHES);
                    $candidate = new Candidate($body);
                    $message = $candidate->result_detail($this->user_id);
                    if(!$message){
                        $this->response_text = self::MENU;
                        $this->stage = self::STATUS_MENU;
                    }
                    else{
                        $this->stage = self::LOGOUT;
                        $this->response_text = $message;
                    }
                break;
                case self::LOGOUT:
                        $this->response_text = self::WELCOME_MESSAGE;
                        $this->stage = self::STATUS_LOGIN;
                        $this->logout();
                break;
                default:
                    $this->response_text = json_decode($this->message_process->obj)->sent;;
                    $this->stage = $this->message_process->stage;
            }
            $obj = array("received"=>$this->message,"sent"=>$this->response_text);
            if(!$this->logout)
                $type = $this->init($this->from,self::POLLING_AGENT,$this->stage,$obj);
            return  $this->response_text;
        }
        public function logout(){
            MessageProcess::where('phone',$this->from)->delete();
            $this->logout=true;
        }
}
