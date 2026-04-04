<?php

namespace App\Http\Controllers\National;

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
use App\Model\Candidate;
use App\Model\ElectionStartupDetail;
use App\Model\PoliticalParty;


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
        ->where('election_result.election_type_id',$NewElectionTypes[0]['id'])
        ->orderBy('candidates.ordering_position','ASC');
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
        if(!isset($dataPoint1) || count($dataPoint1)<=0){
            $dataPoint1 = array(
                array("label"=> "No Results", "y"=> 00.00)
            );
        }
        $dataPoints = $dataPoint1;

        return view('national.home.index',compact('dataPoints'));
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
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.constituency_id = constituency.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.constituency_id = constituency.id) as total_polling"),
            DB::raw("(select count(id) from ElectoralArea where  ElectoralArea.constituency_id = constituency.id) as total_electral")

        )
        /* ->join('constituency', function($join)
        {
           // $join->on('users.id', '=', 'contacts.user_id');
           $join->on('countries','countries.id','=','constituency.country_id');
           $join->on('region','region.id','=','constituency.region_id');
           $join->on('PollingStation','PollingStation.constituency_id','=','constituency.id');
        }); */
        ->join('countries','countries.id','=','constituency.country_id')
        ->join('region','region.id','=','constituency.region_id');
        //->leftJoin('PollingStation','PollingStation.constituency_id','=','constituency.id');
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
        return view("national.home.regionalPresidential");
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
            ->where('election_result.election_type_id',$NewElectionTypes[0]['id']);
            //->where('election_result.region_id',Auth::user()->region_id);

        return DataTables::of($electionResult)->make(true);
    }
    public function ConstituencyResult(){
        return view("national.home.constituencyResult");
    }
    public function ConstituencyResultAajax(){
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
                "constituency.name as constituency_name",
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
            ->join('constituency','constituency.id','=','election_result.region_id')
            ->where('election_result.election_type_id',$NewElectionTypes[0]['id']);
            //->where('election_result.region_id',Auth::user()->region_id);

        return DataTables::of($electionResult)->make(true);
    }

    public function pollingAgent(){
        $UserType = UserType::latest()->first();
        /* $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id', $UserType->id)
            ->get(); */
        //$UserTypes = UserType::orderBy('id','desc')->get();
        //return view('admin.user.Users',compact('Users','UserTypes'));
        $electionTypes = Region::all();
        //return view("admin.polling_agent.polling_agent",compact('electionTypes','UserType'));

        return view("national.agent.agent",compact('electionTypes','UserType'));
    }
    public function pollingAgentAjax(Request $request){
        $UserType = UserType::latest()->first();
        $Users = User::select(
                'users.username',
                'users.secret',
                'users.created_at',
                'users.name as user_name',
                'users.id as user_id',
                'user_type.id as user_type_id',
                'user_type.name as user_type_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "PollingStation.name as PollingStation_name",
                "ElectoralArea.name as ElectoralArea_name"
            )
            ->where('user_type.id', $UserType->id)
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->join('region','region.id','=','users.region_id')
            ->join('constituency','constituency.id','=','users.constituency_id')
            ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
            ->join('PollingStation','PollingStation.id','=','users.polling_station_id');

        //->leftJoin('PollingStation','PollingStation.electoralarea_id','=','ElectoralArea.id');
            if($request->input('electoralarea_id') != "all")
                $Users = $Users->where('users.electoralarea_id',$request->input('electoralarea_id'));
            if($request->input('constituency_id') != "all")
                $Users = $Users->where('users.constituency_id',$request->input('constituency_id'));
            if($request->input('region_id') != "all")
                $Users = $Users->where('users.region_id',$request->input('region_id'));
            if($request->input('polling_station_id') != "all")
                $Users = $Users->where('users.polling_station_id',$request->input('polling_station_id'));



        return DataTables::of($Users)->make(true);
    }
    public function  getConstituency(Request $request){
        $data = $request->all();
        $countries = Constituency::where('region_id',$data['region_id'])->get();
        return $countries;
    }
    public function  getRegion(Request $request){
        $data = $request->all();
        $countries = Region::where('country_id',$data['country_id'])->get();
        return $countries;
    }
    public function  getElectral(Request $request){
        $data = $request->all();
        $countries = ElectoralArea::where('constituency_id',$data['constituency_id'])->get();
        return $countries;
    }
    public function  getPollingStation(Request $request){
        $data = $request->all();
        $countries = PollingStation::where('electoralarea_id',$data['electoralarea_id'])->get();
        return $countries;
    }
    public function candidate($id=false){
        $_electionTypes = ElectionType::all();
        $Constituencies = Constituency::all();
        $regions = Region::all();
        $type = ElectionType::where('id',$id)->first();

        return view('national.agent.candidate',compact('type','id','regions','Constituencies','_electionTypes'));
    }
    public function candidateAjax(Request $request){
        $candidate = Candidate::select(
            'election_type.name as election_type_name',
            'political_party.name as political_party_name',
            'region.name as region_name',
            'constituency.name as constituency_name',
            'PollingStation.name as polling_station_name',
            'candidates.*'
            )
            ->join('election_type','election_type.id','=','candidates.election_id')
            ->join('political_party','political_party.id','=','candidates.party_id')
            ->leftJoin('region','region.id','=','candidates.region_id')
            ->leftJoin('constituency','constituency.id','=','candidates.constituency_id')
            ->leftJoin('PollingStation','PollingStation.id','=','candidates.polling_station_id')
        ->where("candidates.is_disabled",0);
        /* if($request->input('electoralarea_id') != "all")
            $candidate = $candidate->where('candidates.electoralarea_id',$request->input('electoralarea_id'));
        if($request->input('constituency_id') != "all")
            $candidate = $candidate->where('candidates.constituency_id',$request->input('constituency_id'));
        if($request->input('region_id') != "all")
            $candidate = $candidate->where('candidates.region_id',$request->input('region_id')); */
        if($request->input('id') != "none"){

            $candidate = $candidate->where('election_type.id',$request->input('id'));
        }

        if($request->input('election_type_id') != "all")
            $candidate = $candidate->where('election_type.id',$request->input('election_type_id'));
        if($request->input('electoralarea_id') != "all")
            $candidate = $candidate->where('election_type.id',$request->input('electoralarea_id'));
        if($request->input('constituency_id') != "all")
            $candidate = $candidate->where('constituency.id',$request->input('constituency_id'));
        if($request->input('region_id') != "all")
            $candidate = $candidate->where('region.id',$request->input('region_id'));
        return DataTables::of($candidate)->make(true);
    }

    public function  ElectoralArea(){
        /* $regions = ElectoralArea::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "ElectoralArea.*"
            )
            ->join('countries','countries.id','=','ElectoralArea.country_id')
            ->join('region','region.id','=','ElectoralArea.region_id')
            ->join('constituency','constituency.id','=','ElectoralArea.constituency_id')
            ->get(); */
            $regions = Region::orderBy('name','asc')->get();
        return view('national.agent.ElectoralArea',compact('regions'));
    }
    public function electralAajax(Request $request){
        $regions = ElectoralArea::select(
            'countries.id as c_id',
            'countries.name as country_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "ElectoralArea.*",
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.electoralarea_id = ElectoralArea.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.electoralarea_id = ElectoralArea.id) as total_polling")

        )
        ->join('countries','countries.id','=','ElectoralArea.country_id')
        ->join('region','region.id','=','ElectoralArea.region_id')
        ->join('constituency','constituency.id','=','ElectoralArea.constituency_id');
        //->leftJoin('PollingStation','PollingStation.electoralarea_id','=','ElectoralArea.id');

        if($request->input('region_id') != 'all')
            $regions = $regions ->where('ElectoralArea.region_id',$request->input('region_id'));
        if($request->input('constituency_id') != 'all')
            $regions = $regions ->where('ElectoralArea.constituency_id',$request->input('constituency_id'));
        return DataTables::of($regions)->make(true);

    }

    public function  PollingStation(){
        /* $regions = PollingStation::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "ElectoralArea.name as ElectoralArea_name",
                "PollingStation.*"
            )
            ->join('countries','countries.id','=','PollingStation.country_id')
            ->join('region','region.id','=','PollingStation.region_id')
            ->join('constituency','constituency.id','=','PollingStation.constituency_id')
            ->join('ElectoralArea','ElectoralArea.id','=','PollingStation.electoralarea_id')
            ->get(); */
            $regions = Region::orderBy('name','asc')->get();
        return view('national.agent.Polling',compact('regions'));
    }
    public function pollingStationAajax(Request $request){
        $regions = PollingStation::select(
            'countries.id as c_id',
            'countries.name as country_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.*"
        )
        ->join('countries','countries.id','=','PollingStation.country_id')
        ->join('region','region.id','=','PollingStation.region_id')
        ->join('constituency','constituency.id','=','PollingStation.constituency_id')
        ->leftJoin('ElectoralArea','ElectoralArea.id','=','PollingStation.electoralarea_id');
        if($request->input('region_id') != 'all')
            $regions = $regions ->where('PollingStation.region_id',$request->input('region_id'));
        if($request->input('constituency_id') != 'all')
            $regions = $regions ->where('PollingStation.constituency_id',$request->input('constituency_id'));

            if($request->input('electoralarea_id') != 'all')
            $regions = $regions ->where('PollingStation.electoralarea_id',$request->input('electoralarea_id'));
        return DataTables::of($regions)->make(true);
    }
    public function profile(){

        $user = User::select(
            'users.username',
            'users.secret',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name'
            //'region.name as region_name',
            //"constituency.name as constituency_name"
            //"PollingStation.name as PollingStation_name",
            //"ElectoralArea.name as ElectoralArea_name"
            //"PollingStation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        //->join('region','region.id','=','users.region_id')
        //->join('constituency','constituency.id','=','users.constituency_id')
        //->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
        //->join('PollingStation','PollingStation.id','=','users.polling_station_id')
        ->first();
        //dd($user->toArray());
        return view('director.candidate.profile',compact('user'));
    }

    public function result($id){
        $regions = Region::all();
        $election = ElectionType::find($id);
        $details = ElectionStartupDetail::where('election_type_id',$id)->get();
        return view("national.home.result",compact('election','id','details','regions'));
    }
    public function allResultAjax(Request $request){
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
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
            "election_result.obtained_votes as election_result"

            //DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.election_result_id = election_result.id) as election_result"),
            /* DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result
            where party_election_result.candidate_id = candidates.id
            and party_election_result.election_result_id = election_result.id
            ) as party_election_result_obtained_vote") */
            //

        )
           ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id');
        if ($request->input('election_type_id') != "all") {
                $electionResult = $electionResult->where('election_result.election_type_id', $request->input('election_type_id'));
        }
        if ($request->input('election_start_up_id') != "all") {
            $electionResult = $electionResult->where('election_result.election_start_up_id', $request->input('election_start_up_id'));
            /* $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        }

        if ($request->input('region_id') != "all") {
            /* $electionResult = ElectionResult::select(
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
                "election_result.obtained_votes as election_result"
                )
               ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
                ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
                ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id'); */
            $electionResult = $electionResult->where('party_election_result.region_id', $request->input('region_id'));
            /* $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->where('party_election_result.region_id', $request->input('region_id'))
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        }
        if ($request->input('constituency_id') != "all") {

            $electionResult = $electionResult->where('party_election_result.constituency_id', $request->input('constituency_id'));
            /* $electionResult = $electionResult->groupBy('political_party.party_initial')
        ->where('party_election_result.constituency_id', $request->input('constituency_id'))
        ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        //dd($electionResult->toArray());
    }
    if($request->input('electoralarea_id') != "all"){
        $electionResult = $electionResult->where('election_result.electoral_area_id',$request->input('electoralarea_id'));

      }
        if ($request->input('polling_station_id') != "all") {
            /* $electionResult = ElectionResult::select(
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
                "election_result.obtained_votes as election_result"
                )
               ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
                ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
                ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id'); */
            $electionResult = $electionResult->where('party_election_result.polling_station_id', $request->input('polling_station_id'));
        }


        $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
        $electionResults = $electionResult->get();
        //dd($electionResults->toArray());
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));
        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->party_election_result_obtained_vote), "y" => (($electionResult->party_election_result_obtained_vote * 100) / $total));
        }

        $dataPoints = array_unique($dataPoints, SORT_REGULAR);
        $dataPoints1 = [];
        foreach ($dataPoints as $dataPoint) {
            $dataPoints1[] = $dataPoint;
        }
        if (count($dataPoints1) <= 0) {
            $dataPoints1 = array(
                array("label" => "No Results", "y" => 00.00)
            );
        }
        $dataPoints = $dataPoints1;
        return $dataPoints;
        /* $electionTypes = ElectionType::all();

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
            "election_result.obtained_votes as election_result",
            "party_election_result.election_result_id",
            "party_election_result.party_id",
            "party_election_result.candidate_id"
            //DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")
            //DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.election_result_id = election_result.id) as party_election_result_obtained_vote")

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id');
        //->where('election_result.election_type_id',$NewElectionTypes[0]['id']);
        if($request->input('election_start_up_id') != "all")
            $electionResult = $electionResult->where('election_result.election_start_up_id',$request->input('election_start_up_id'));
        if($request->input('polling_station_id') != "all")
            $electionResult = $electionResult->where('election_result.polling_station_id',$request->input('polling_station_id'));
        if($request->input('electoralarea_id') != "all")
            $electionResult = $electionResult->where('election_result.electoral_area_id',$request->input('electoralarea_id'));
       if($request->input('region_id') != "all"){
            $electionResult = $electionResult->groupBy('party_initial');
            $electionResult = $electionResult->where('election_result.region_id',$request->input('region_id'));
       }
        if($request->input('constituency_id') != "all")
            $electionResult = $electionResult->where('election_result.constituency_id',$request->input('constituency_id'));
        if($request->input('election_type_id') != "all")
            $electionResult = $electionResult->where('election_result.election_type_id',$request->input('election_type_id'));

        $electionResults =$electionResult->get();
        //dd($electionResults->toArray());
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

       $dataPoints = [];
        foreach($electionResults as $electionResult){
              $dataPoints[] =   array("label"=> $electionResult->party_initial."  -  ".number_format($electionResult->party_election_result_obtained_vote )  , "y"=> (($electionResult->party_election_result_obtained_vote*100)/$total));
        }
        $dataPoints = array_unique($dataPoints,SORT_REGULAR);
       $dataPoints1 = [];
        foreach($dataPoints as $dataPoint ){
            $dataPoints1[]=$dataPoint ;
        }
        if(count($dataPoints1)<=0){
            $dataPoints1 = array(
                array("label"=> "No Results", "y"=> 00.00)
            );
        }
        $dataPoints = $dataPoints1;
        return $dataPoints ; */
    }
    public function  getPoliticalParty(Request $request){
        $data = $request->all();
        $PoliticalParties = PoliticalParty::select('candidates.election_id','candidates.party_id','candidates.first_name','political_party.id','political_party.name')
            ->leftJoin('candidates', function($join) use ($data)
            {
                $join->on('candidates.party_id' , '=','political_party.id')
                ->where('candidates.constituency_id','=',$data['constituency_id']);
            });
            $PoliticalParties = $PoliticalParties->whereNull('candidates.party_id');
            $PoliticalParties = $PoliticalParties->get();

        return $PoliticalParties;
    }

    public function  getPoliticalPartyByElectionType(Request $request){
        $data = $request->all();
        $PoliticalParties = PoliticalParty::select('candidates.election_id','candidates.party_id','candidates.first_name','political_party.id','political_party.name')
            ->leftJoin('candidates', function($join) use ($data)
            {
                $join->on('candidates.party_id' , '=','political_party.id')
                ->where('candidates.election_start_up_id','=',$data['election_start_up_id']);
            });
            $PoliticalParties = $PoliticalParties->whereNull('candidates.party_id');
            $PoliticalParties = $PoliticalParties->get();

        return $PoliticalParties;
    }
}
