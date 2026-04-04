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
use App\Model\PoliticalParty;
use Illuminate\Support\Facades\Auth;
use App\Model\ElectionResult;
use App\Model\PartyElectionResult;
use App\Model\ElectionType;
use App\Model\Candidate as CandidateModel;

class Candidate implements Constant
{
    public $body;
    public $message = "Reply with this formate .\n R*rejected_votes,candidate_no*obtained_votes\n e.g R*1,1*500 \n";
    public function __construct($body)
    {
        $this->body = $body;
    }
    public function candidate($user_id)
    {
        $body = $this->body;

        $user = User::find($user_id);
        if (!$user) {
            return false;
        }
        $election_start_up = $body;
        $electionStartupDetail = ElectionStartupDetail::select('election_type.name', 'election_startup_detail.*')
            ->join('election_type', 'election_type.id', '=', 'election_startup_detail.election_type_id')
            ->where("election_startup_detail.id", $election_start_up)
            ->where("status", 1)
            ->first();
        if (!$electionStartupDetail) {
            return false;
        }

        $parties = PoliticalParty::select(
            'candidates.first_name',
            'candidates.last_name',
            'candidates.party_id',
            'candidates.id as candidate_id',
            'political_party.party_initial'
        )
            ->join('candidates', 'political_party.id', '=', 'candidates.party_id')
            ->orderBy('candidates.ordering_position', 'ASC');
        if ($electionStartupDetail->election_type_id != 1) {
            $parties = $parties->where('candidates.election_id', $electionStartupDetail->election_type_id);
            if ($electionStartupDetail->election_type_id != 2) {
                $parties = $parties->where('candidates.polling_station_id', $user->polling_station_id);
            }
            if ($electionStartupDetail->election_type_id == 2) {
                $parties = $parties->where('candidates.constituency_id', $user->constituency_id);
            }
        }
        if ($electionStartupDetail->election_type_id == 1) {
            $parties = $parties->whereNull('candidates.region_id');
            $parties = $parties->whereNull('candidates.constituency_id');
            $parties = $parties->whereNull('candidates.polling_station_id');
            $parties = $parties->whereNull('candidates.electoral_area_id');
        }
        $parties->where('candidates.election_start_up_id', $electionStartupDetail->id);
        $parties = $parties->get();
        $this->message.="\n";
        foreach ($parties as $key => $party) {
            # code...
            $this->message .= $party->candidate_id . " : " . $party->party_initial . " - " . $party->first_name." ".$party->last_name. "\n\n";

        }
        return $this->message;
    }
    public function updateResult($user_id, $data)
    {

        $data = json_decode($data, true);
        if (isset($data['election_start_up_id']) && $data['election_start_up_id']) {
            $election_start_up = $data['election_start_up_id'];
        } else {
            return false;
        }
        $body = $this->body;
        $details = explode(',', $body);
        $data_for_validation=$this->verify_election($user_id,$election_start_up);
        $rejected_vote = 0;
        foreach ($details as $key => $detail) {

            $rows = explode('*', $detail);
            if (!isset($rows[1])) {
                return $this->message .= "Your message formate was not correct.";
            }
            if (!preg_match('/^\d+$/', $rows[1])) {
                return $this->message .= "Your message formate was not correct.";
            }

            if ($key == 0) {


                if ((!isset($rows[0]) && !$rows[0]) && (!isset($rows[1]) && !$rows[1])) {

                    return $this->message .= "Your message formate was not correct.";
                } else if (strtoupper($rows[0]) != "R") {

                    return $this->message .= "Your message formate was not correct.";

                }  else {
                    $rejected_vote = $rows[1];
                }
            } else {
                if (!preg_match('/^\d+$/', $rows[0])) {
                    return $this->message .= "Your message formate was not correct.";
                }

//                dd(array_search((int)$rows[0],array_column($data_for_validation,'candidate_id')));

                if(array_search((int)$rows[0],array_column($data_for_validation,'candidate_id')) ===false){

                    return $this->message .= "Wrong Candidate Number.";
                };
            }
        }
        /* ==================== user result ======================
        ==================== ///\\\\\\\]] ====================== */
        $user = User::select(
            'users.id as user_id',
            'users.*',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "PollingStation.name as PollingStation_name",
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.polling_station_id as PollingStation_Id",
            "users.country_id"
        )
        ->where('users.id',$user_id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')->first();

        $electionStartupDetail = ElectionStartupDetail::select(
            'election_type.name',
            'election_startup_detail.*'
    )
    ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
    ->where("election_startup_detail.id",$election_start_up)

    ->where("status",1)->first();
        $check_result =  $this->verify_result_if_available($user_id,$election_start_up);

        if($check_result && isset($check_result->toArray()[0]['id'])){
            $e_r = ElectionResult::where('id',$check_result->toArray()[0]['id'])
                ->where('election_result.election_start_up_id',$election_start_up)
                ->first();
                if($e_r->verify_by_constituency === 1){
                    $this->message = "verified";
                    return $this->message;
                }
            $e_r->total_ballot =0;
            $e_r->total_rejected_ballot = $rejected_vote;
            $e_r->save();
            foreach ($details as $key => $detail) {

                $rows = explode('*', $detail);

                if ($key == 0) {

                } else {
                    $partyElectionResult = PartyElectionResult::where('election_result_id',$e_r->id)->where('candidate_id',$rows[0])->first();
                    $partyElectionResult->obtained_vote = $rows[1];
                            $partyElectionResult->save();
                    /* $partyElectionResult = new PartyElectionResult;
                    $partyElectionResult->user_id = $user->id;
                    $partyElectionResult->election_result_id = $electionResult->id;
                    $partyElectionResult->polling_station_id = $electionResult->polling_station_id;

                    $partyElectionResult->country_id = $electionResult->country_id;
                    $partyElectionResult->region_id = $electionResult->region_id;
                    $partyElectionResult->constituency_id = $electionResult->constituency_id;
                    $partyElectionResult->electoral_area_id	 = $electionResult->electoralarea_id;

                    $cand = CandidateModel::find($rows[0]);
                    $partyElectionResult->party_id = $cand->party_id;
                            $partyElectionResult->candidate_id = $rows[0];
                            $partyElectionResult->obtained_vote = $rows[1];
                            $partyElectionResult->save(); */
                }
            }
            $e_r->obtained_votes = PartyElectionResult::where('election_result_id',$e_r->id)
                //->where('user_id', Auth::user()->id)
                ->where('polling_station_id', $e_r->polling_station_id)->sum('obtained_vote');
            $e_r->save();
            $e_r->total_ballot = $e_r->obtained_votes +   $e_r->total_rejected_ballot;
            $e_r->save();
        }else{

            $electionResult = new ElectionResult;
            $electionResult->polling_station_id =  $user->polling_station_id;
            $electionResult->user_id = $user->id;
            $electionResult->user_type_id = $user->user_type_id;
            $electionResult->country_id = $user->country_id;
            $electionResult->region_id = $user->region_id;
            $electionResult->constituency_id = $user->constituency_id;
            $electionResult->electoral_area_id	 = $user->electoralarea_id;


            $electionResult->election_type_id = $electionStartupDetail->election_type_id;
            $electionResult->election_start_up_id = $electionStartupDetail->id;
            $electionResult->obtained_votes = 0;

            $electionResult->total_ballot = 0;
            $electionResult->total_rejected_ballot =$rejected_vote;
            $electionResult->save();


            foreach ($details as $key => $detail) {

                $rows = explode('*', $detail);

                if ($key == 0) {

                } else {
                    $partyElectionResult = new PartyElectionResult;
                    $partyElectionResult->user_id = $user->id;
                    $partyElectionResult->election_result_id = $electionResult->id;
                    $partyElectionResult->polling_station_id = $electionResult->polling_station_id;

                    $partyElectionResult->country_id = $electionResult->country_id;
                    $partyElectionResult->region_id = $electionResult->region_id;
                    $partyElectionResult->constituency_id = $electionResult->constituency_id;
                    $partyElectionResult->electoral_area_id	 = $electionResult->electoralarea_id;

                    $cand = CandidateModel::find($rows[0]);
                    $partyElectionResult->party_id = $cand->party_id;
                            $partyElectionResult->candidate_id = $rows[0];
                            $partyElectionResult->obtained_vote = $rows[1];
                            $partyElectionResult->save();
                }
            }

            $electionResult->obtained_votes = PartyElectionResult::where('election_result_id',$electionResult->id)
                ->where('user_id', $user->id)
                ->where('polling_station_id', $electionResult->polling_station_id)->sum('obtained_vote');

            $electionResult->save();
            $electionResult->total_ballot = $electionResult->obtained_votes +   $electionResult->total_rejected_ballot;
            $electionResult->save();
        }

 /* ====================  End user result ======================
        ==================== ///\\\\\\\]] ====================== */

        // /dd($rejected_vote);
        $this->message = "Result Updated successfully. You are logout now.";
        return $this->message;
    }
    public function verify_election($user_id,$election_start_up=false)
    {

        $user = User::find($user_id);
        if (!$user) {
            return false;
        }

        $electionStartupDetail = ElectionStartupDetail::select('election_type.name', 'election_startup_detail.*')
            ->join('election_type', 'election_type.id', '=', 'election_startup_detail.election_type_id')
            ->where("election_startup_detail.id", $election_start_up)
            ->where("status", 1)
            ->first();
        if (!$electionStartupDetail) {
            return false;
        }

        $parties = PoliticalParty::select(
            'candidates.id as candidate_id'
        )
            ->join('candidates', 'political_party.id', '=', 'candidates.party_id')
            ->orderBy('candidates.ordering_position', 'ASC');
        if ($electionStartupDetail->election_type_id != 1) {
            $parties = $parties->where('candidates.election_id', $electionStartupDetail->election_type_id);
            if ($electionStartupDetail->election_type_id != 2) {
                $parties = $parties->where('candidates.polling_station_id', $user->polling_station_id);
            }
            if ($electionStartupDetail->election_type_id == 2) {
                $parties = $parties->where('candidates.constituency_id', $user->constituency_id);
            }
        }
        if ($electionStartupDetail->election_type_id == 1) {
            $parties = $parties->whereNull('candidates.region_id');
            $parties = $parties->whereNull('candidates.constituency_id');
            $parties = $parties->whereNull('candidates.polling_station_id');
            $parties = $parties->whereNull('candidates.electoral_area_id');
        }
        $parties->where('candidates.election_start_up_id', $electionStartupDetail->id);
        $parties = $parties->get();

        return $parties->toArray();
    }
    public function result_detail($user_id,$election_result_id=false)
    {
        $body = $this->body;

        if (!$user_id) {
            return false;
        }
        $election_start_up = $body;
        $electionStartupDetail = ElectionStartupDetail::select('election_type.name', 'election_startup_detail.*')
            ->join('election_type', 'election_type.id', '=', 'election_startup_detail.election_type_id')
            ->where("election_startup_detail.id", $election_start_up)
            ->where("status", 1)
            ->first();
        if (!$electionStartupDetail) {
            return false;
        }

        $user = User::select(
            'users.username',
            'users.*',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "PollingStation.name as PollingStation_name",
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', $user_id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')->first();

        $electionStartupDetail = ElectionStartupDetail::select('election_type.name','election_startup_detail.*')
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("election_startup_detail.id",$election_start_up)
            ->where("status",1)
            ->first();
        if(!$electionStartupDetail){
            return false;
        }

        $parties = PoliticalParty::select(
            'candidates.first_name',
            'candidates.last_name',
            'candidates.id as candidate_id',
            'political_party.party_initial',
            'political_party.id as political_party_id'
        )
        ->join('candidates','political_party.id','=','candidates.party_id')
        ->orderBy('candidates.ordering_position','ASC');
        if($electionStartupDetail->election_type_id != 1){
            $parties = $parties->where('candidates.election_id',$electionStartupDetail->election_type_id);
            if($electionStartupDetail->election_type_id != 2 ){
                    $parties = $parties->where('candidates.polling_station_id',$user->polling_station_id);            }
            if($electionStartupDetail->election_type_id == 2){
                $parties = $parties->where('candidates.constituency_id',$user->constituency_id);
            }
        }
        if($electionStartupDetail->election_type_id == 1){
            $parties = $parties->whereNull('candidates.region_id');
            $parties = $parties->whereNull('candidates.constituency_id');
            $parties = $parties->whereNull('candidates.polling_station_id');
            $parties = $parties->whereNull('candidates.electoral_area_id');
        }
        $parties = $parties->get();
        //dd($electionStartupDetail->election_type_id,$election_start_up,$user->polling_station_id);
        $electionResult = ElectionResult::select(
            "party_election_result.party_id as party_election_result_party_id",
            "party_election_result.candidate_id as party_election_result_candidate_id",
            "party_election_result.obtained_vote as party_election_result_obtained_vote",
            "election_result.id",
            "political_party.party_initial",
            "candidates.first_name",
            "candidates.last_name",
            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_result.obtained_votes",
            "PollingStation.total_voters"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('election_result.user_id',$user->id)
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        //->where('candidates.polling_station_id',$user->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)

        ->where('election_result.election_start_up_id',$election_start_up)
        ->where('election_result.polling_station_id',$user->polling_station_id)
        ->orderBy('candidates.ordering_position','ASC');


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }
        $electionResult =$electionResult->get();
        //dd($electionResult->toArray());
        if(!$electionResult){
            return false;
        }
        $msg = "Detail \n\n Rejected Votes: ".@$electionResult->toArray()[0]['total_rejected_ballot']."\n\n";
        foreach ($electionResult as $key => $result) {
            $msg.= $result->party_initial."-".$result->first_name." ".$result->last_name." : ".$result->party_election_result_obtained_vote."\n\n";

        }
        return $msg;
        //dd($electionResult->toArray());
    }
    public function verify_result_if_available($user_id,$election_start_up,$election_result_id=false)
    {
        $body = $this->body;

        if (!$user_id) {
            return false;
        }

        $electionStartupDetail = ElectionStartupDetail::select('election_type.name', 'election_startup_detail.*')
            ->join('election_type', 'election_type.id', '=', 'election_startup_detail.election_type_id')
            ->where("election_startup_detail.id", $election_start_up)
            ->where("status", 1)
            ->first();

        if (!$electionStartupDetail) {
            return false;
        }
        $user = User::select(
            'users.username',
            'users.*',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "PollingStation.name as PollingStation_name",
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', $user_id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')->first();

        $electionStartupDetail = ElectionStartupDetail::select('election_type.name','election_startup_detail.*')
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("election_startup_detail.id",$election_start_up)
            ->where("status",1)
            ->first();

        if(!$electionStartupDetail){
            return false;
        }

        $parties = PoliticalParty::select(
            'candidates.first_name',
            'candidates.last_name',
            'candidates.id as candidate_id',
            'political_party.party_initial',
            'political_party.id as political_party_id'
        )
        ->join('candidates','political_party.id','=','candidates.party_id')
        ->orderBy('candidates.ordering_position','ASC');
        if($electionStartupDetail->election_type_id != 1){
            $parties = $parties->where('candidates.election_id',$electionStartupDetail->election_type_id);
            if($electionStartupDetail->election_type_id != 2 ){
                    $parties = $parties->where('candidates.polling_station_id',$user->polling_station_id);            }
            if($electionStartupDetail->election_type_id == 2){
                $parties = $parties->where('candidates.constituency_id',$user->constituency_id);
            }
        }
        if($electionStartupDetail->election_type_id == 1){
            $parties = $parties->whereNull('candidates.region_id');
            $parties = $parties->whereNull('candidates.constituency_id');
            $parties = $parties->whereNull('candidates.polling_station_id');
            $parties = $parties->whereNull('candidates.electoral_area_id');
        }
        $parties = $parties->get();
        //dd($electionStartupDetail->election_type_id,$election_start_up,$user->polling_station_id);
        $electionResult = ElectionResult::select(
            "party_election_result.party_id as party_election_result_party_id",
            "party_election_result.candidate_id as party_election_result_candidate_id",
            "party_election_result.obtained_vote as party_election_result_obtained_vote",
            "election_result.id",
            "political_party.party_initial",
            "candidates.first_name",
            "candidates.last_name",
            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_result.obtained_votes",
            "PollingStation.total_voters"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('election_result.user_id',$user->id)
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        //->where('candidates.polling_station_id',$user->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)

        ->where('election_result.election_start_up_id',$election_start_up)
        ->where('election_result.polling_station_id',$user->polling_station_id)
        ->orderBy('candidates.ordering_position','ASC');


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }
        $electionResult =$electionResult->get();

        if(!$electionResult){
            return false;
        }
        return $electionResult;
        //dd($electionResult->toArray());
    }
}
