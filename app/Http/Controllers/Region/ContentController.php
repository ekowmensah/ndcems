<?php

namespace App\Http\Controllers\Region;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\Model\UserType;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Model\Country;
use App\Model\Region;
use App\Model\Constituency;
use App\Model\ElectoralArea;
use App\Model\PollingStation;
use DataTables;
use DB;
use App\Model\ElectionResult;
use App\Model\ElectionType;


class ContentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function  dashboard(){

        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
        }
        $electionResult = ElectionResult::select(
            "party_election_result.obtained_vote as party_election_result_obtained_vote",
            "election_result.id",
            "political_party.party_initial",
            "political_party.id as political_party_id",
            "candidates.first_name",
            "candidates.last_name",
            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_result.obtained_votes as obtained_votes",
            DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->where('election_result.election_type_id',$NewElectionTypes[0]['id']);
        $electionResults =$electionResult->get();
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

       $dataPoints = [];
        foreach($electionResults as $electionResult){
              $dataPoints[] =   array("label"=> $electionResult->party_initial."  -  ".$electionResult->election_result  , "y"=> (($electionResult->election_result*100)/$total));
        }
        $dataPoints = array_unique($dataPoints,SORT_REGULAR);
       $dataPoint1 = [];
        foreach($dataPoints as $dataPoint ){
            $dataPoint1[]=$dataPoint ;
        }
        if(count($dataPoint1)<=0){
            $dataPoint1 = array(
                array("label"=> "No Results", "y"=> 00.00)
            );
        }
        $dataPoints = $dataPoint1;

        return view('region.home.index',compact('dataPoints'));
    }
    public function presidentialResultAjax(){
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
        }
        $electionResult = ElectionResult::select(
            "party_election_result.obtained_vote as party_election_result_obtained_vote",
            "election_result.id",
            "political_party.party_initial",
            "political_party.id as political_party_id",
            "candidates.first_name",
            "candidates.last_name",
            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_result.obtained_votes as obtained_votes",
            DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->where('election_result.election_type_id',$NewElectionTypes[0]['id']);
        $electionResults =$electionResult->get();
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

       $dataPoints = [];
        foreach($electionResults as $electionResult){
              $dataPoints[] =   array("label"=> $electionResult->party_initial."  -  ".$electionResult->election_result  , "y"=> (($electionResult->election_result*100)/$total));
        }
        $dataPoints = array_unique($dataPoints,SORT_REGULAR);
       $dataPoints1 = [];
        foreach($dataPoints as $dataPoint ){
            $dataPoint1[]=$dataPoint ;
        }
        $dataPoints = $dataPoint1;
        return $dataPoints;
    }
    public function Presidential(){
        return view('region.home.presidential');

    }
    public function constituencyAajax(Request $request ){
        $regions = Constituency::select(
            'countries.id as c_id',
            'countries.name as country_name',

            "constituency.id",
            "constituency.name",
            'region.name as region_name',
            DB::raw("(select sum(total_voters) from pollingstation where  pollingstation.constituency_id = constituency.id) as total_voters"),
            DB::raw("(select count(id) from pollingstation where  pollingstation.constituency_id = constituency.id) as total_polling"),
            DB::raw("(select count(id) from electoralarea where  electoralarea.constituency_id = constituency.id) as total_electral")
            /* "election_result.verify_by_constituency",
            "election_result.verify_by_regional" */
        )
        /* ->join('constituency', function($join)
        {
           // $join->on('users.id', '=', 'contacts.user_id');
           $join->on('countries','countries.id','=','constituency.country_id');
           $join->on('region','region.id','=','constituency.region_id');
           $join->on('pollingstation','pollingstation.constituency_id','=','constituency.id');
        }); */
        //->join('election_result','election_result.constituency_id','=','constituency.id')
        ->join('countries','countries.id','=','constituency.country_id')
        ->join('region','region.id','=','constituency.region_id');
        //->leftJoin('pollingstation','pollingstation.constituency_id','=','constituency.id');
        //if($request->input('region_id') != 'all')
            $regions = $regions ->where('constituency.region_id',Auth::user()->region_id);
        return DataTables::of($regions)->make(true);

    }
    public function constituencyView($id)
    {
        $constituency_detail = Constituency::find($id);
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
        }

        $electionResult = ElectionResult::select(
            "party_election_result.obtained_vote as party_election_result_obtained_vote",
            "election_result.id",
            "political_party.party_initial",
            "political_party.id as political_party_id",
            "candidates.first_name",
            "candidates.last_name",
            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_result.obtained_votes as obtained_votes",
            DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->where('election_result.election_type_id',$NewElectionTypes[1]['id'])
        ->where('election_result.verify_by_constituency',1)
        ->where('election_result.constituency_id',$id);
        $electionResults =$electionResult->get();
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

       $dataPoints = [];
        foreach($electionResults as $electionResult){
              $dataPoints[] =   array("label"=> $electionResult->party_initial."  -  ".$electionResult->election_result  , "y"=> (($electionResult->election_result*100)/$total));
        }
        $dataPoints = array_unique($dataPoints,SORT_REGULAR);

        $dataPoint1 = [];
        foreach($dataPoints as $dataPoint ){
            $dataPoint1[]=$dataPoint ;
        }

        if(count($dataPoint1)<=0){
            $dataPoints1 = array(
                array("label"=> "No Results", "y"=> 00.00)
            );
        }
        $dataPoints = $dataPoint1;
        return view("region.home.presidentialResultView",compact('constituency_detail','dataPoints'));
    }
    public function PresidentialResult(){
        return view("region.home.regionalPresidential");
    }
    public function PresidentialAajax(){
            $electionTypes = ElectionType::all();

            $NewElectionTypes = [];
            foreach ($electionTypes->toArray() as $i => $value) {
                $data = [
                        "index"=>$i
                ];
                $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
            }
            $electionResult = ElectionResult::select(
                "election_result.id",
                "election_result.region_id",
                "region.name as region_name",
                "party_election_result.obtained_vote as party_election_result_obtained_vote",
                "political_party.party_initial",
                "political_party.id as political_party_id",
                "candidates.first_name",
                "candidates.last_name",
                "election_result.total_ballot",
                "election_result.total_rejected_ballot",
                "election_result.election_start_up_id",
                "election_result.obtained_votes as obtained_votes",
                DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

            )
            ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
            ->join('political_party','political_party.id','=','party_election_result.party_id')
            ->join('candidates','candidates.id','=','party_election_result.candidate_id')
            ->join('region','region.id','=','election_result.region_id')
            ->where('election_result.election_type_id',$NewElectionTypes[0]['id'])
            ->where('election_result.region_id',Auth::user()->region_id);

        return DataTables::of($electionResult)->make(true);
    }
    public function profile(){

        $user = User::select(
            'users.username',
            'users.secret',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name'
            //"constituency.name as constituency_name"
            //"pollingstation.name as PollingStation_name",
            //"electoralarea.name as ElectoralArea_name"
            //"pollingstation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        //->join('constituency','constituency.id','=','users.constituency_id')
        //->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
        //->join('pollingstation','pollingstation.id','=','users.polling_station_id')
        ->first();
        //dd($user->toArray());
        return view('region.home.profile',compact('user'));
    }

    public function regionalResultView($id,$regional_id)
    {
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
        }

        $electionResult = ElectionResult::select(
            "party_election_result.obtained_vote as party_election_result_obtained_vote",
            "election_result.id",
            "political_party.party_initial",
            "political_party.id as political_party_id",
            "candidates.first_name",
            "candidates.last_name",
            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_result.obtained_votes as obtained_votes",
            DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->where('election_result.election_type_id',$NewElectionTypes[1]['id'])
        ->where('election_result.verify_by_constituency',1)
        ->where('election_result.id',$id)
        ;
        $electionResults =$electionResult->get();
        $constituency_detail = Region::find($regional_id);

        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

       $dataPoints = [];
        foreach($electionResults as $electionResult){
              $dataPoints[] =   array("label"=> $electionResult->party_initial."  -  ".$electionResult->election_result  , "y"=> (($electionResult->election_result*100)/$total));
        }
        $dataPoints = array_unique($dataPoints,SORT_REGULAR);

        $dataPoint1 = [];
        foreach($dataPoints as $dataPoint ){
            $dataPoint1[]=$dataPoint ;
        }

        if(count($dataPoint1)<=0){
            $dataPoints1 = array(
                array("label"=> "No Results", "y"=> 00.00)
            );
        }
        $dataPoints = $dataPoint1;
        return view("region.home.RegionalResultView",compact('constituency_detail','dataPoints'));
    }
}

