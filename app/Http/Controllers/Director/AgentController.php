<?php

namespace App\Http\Controllers\Director;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Model\ElectionStartupDetail;
use App\Model\PoliticalParty;
use App\Model\ElectionResult;
use App\Model\PartyElectionResult;
use App\Model\ElectionType;
use App\Model\PollingStation;

class AgentController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($election_start_up,$election_result_id=false ,$polling_station_id=false, Request $request)
    {
        if($polling_station_id=="null"){
            $electionResult = ElectionResult::find($election_result_id);
            $polling_station_id = $electionResult->polling_station_id;
        }
        $pollingStation = PollingStation::find($polling_station_id);
        ///dd($polling_station_id);

        /* $user = User::select(
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
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.polling_station_id as PollingStation_Id",
            "PollingStation.total_voters"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')->first(); */
        $polling = PollingStation::find($polling_station_id);
        $electionStartupDetail = ElectionStartupDetail::select('election_type.name','election_startup_detail.*')
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("election_startup_detail.id",$election_start_up)
            ->where("status",1)
            ->first();
        if(!$electionStartupDetail){
            $request->session()->flash('error', ' Something went wrong!');

            return redirect(route("Agent.election"));
        }


        $parties = PoliticalParty::select(
            'candidates.first_name',
            'candidates.last_name',
            'candidates.id as candidate_id',
            'political_party.party_initial',
            'political_party.id as political_party_id'
        )
        ->join('candidates','political_party.id','=','candidates.party_id')
        ->orderBy('candidates.ordering_position', 'ASC');
        if($electionStartupDetail->election_type_id != 1){
            $parties = $parties->where('candidates.election_id',$electionStartupDetail->election_type_id);
            if($electionStartupDetail->election_type_id != 2 ){
                    $parties = $parties->where('candidates.polling_station_id',Auth::user()->polling_station_id);            }
            if($electionStartupDetail->election_type_id == 2){
                $parties = $parties->where('candidates.constituency_id',Auth::user()->constituency_id);
            }
        }
        if($electionStartupDetail->election_type_id == 1){
            $parties = $parties->whereNull('candidates.region_id');
            $parties = $parties->whereNull('candidates.constituency_id');
            $parties = $parties->whereNull('candidates.polling_station_id');
            $parties = $parties->whereNull('candidates.electoral_area_id');
        }
        $parties->where('candidates.election_start_up_id',$electionStartupDetail->id);

        $parties = $parties->get();
   //     dd($parties->toArray());

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
            "election_result.obtained_votes"


        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        //->where('candidates.election_id',$electionStartupDetail->election_type_id)
        //->where('candidates.election_start_up_id',$electionStartupDetail->id)
        //->where('election_result.user_id',Auth::user()->id)

        //->orWhere('election_result.result_by_constituency',Auth::user()->id)
        ->where('election_result.election_start_up_id',$election_start_up)
        ->orderBy('candidates.ordering_position','ASC')
        ;
          //  dd($electionResult->toArray());

        if( $election_result_id && $election_result_id != "null"){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);

        }else{
            $electionResult = $electionResult->where('election_result.polling_station_id',$polling_station_id);

        }
        $electionResult =$electionResult->get();

       //dd($electionResult->toArray());

        return view('director.home.agent_index',compact('pollingStation','election_result_id','polling','election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
    public function captureResult($election_start_up,Request $request){

       /*  $user = User::select(
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "PollingStation.name as PollingStation_name",
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.polling_station_id as PollingStation_Id",
            "users.country_id"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('ElectoralArea','ElectoralArea.id','=','users.electoralarea_id')
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')->first(); */

        $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
        )
        ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
        ->where("election_startup_detail.id",$election_start_up)

        ->where("status",1)->first();
        $posted_Data = $request->all();
        //dd($electionStartupDetail->toArray());


        $e_r = ElectionResult::where('election_result.election_start_up_id',$election_start_up)
         ->where('id',$request->input('election_result_id'))
        // ->where('election_result.id',$electionStartupDetail->election_type_id)
            ->first();
        //dd($request->all());
        if($e_r){
            $e_r->total_ballot =0;
            $e_r->total_rejected_ballot = $request->input('total_rejected_ballot');
            $e_r->save();

            foreach ($posted_Data['party'] as $key => $value) {
                $party_id = key($value);
                $partyElectionResult = PartyElectionResult:://where('result_by_constituency',Auth::user()->id)
                    where('election_result_id',$e_r->id)
                    //->where('polling_station_id',$e_r->polling_station_id)
                    ->where('party_id',$party_id);

                    foreach($value as $key => $_value){
                        $candidate_id = key($_value);
                        $partyElectionResult = $partyElectionResult->where('election_result_id',$e_r->id)->where('candidate_id', key($_value))->first();
                        //dd($partyElectionResult->toArray());

                        foreach($_value as $key => $__value){
                            $obtained_vote =  $__value;
                            $partyElectionResult->obtained_vote = $__value;
                            $partyElectionResult->save();
                        }

                    }
                $partyElectionResult->save();
            }
            $e_r->obtained_votes = PartyElectionResult::where('election_result_id',$e_r->id)
                ->where('result_by_constituency', Auth::user()->id)
                ->where('polling_station_id', $e_r->polling_station_id)->sum('obtained_vote');
            $e_r->save();
            $e_r->total_ballot = $e_r->obtained_votes +   $e_r->total_rejected_ballot;
            $e_r->save();
            $request->session()->flash('message', ' Election Result updated successfully!');


        }else{
          //  dd($request->all());
            $electionResult = new ElectionResult;
            $electionResult->polling_station_id =  $request->input('polling_station_id');
            $electionResult->result_by_constituency = Auth::user()->id;
            $electionResult->user_type_id = Auth::user()->user_type_id;
            $electionResult->country_id = $request->input('country_id');
            $electionResult->region_id = $request->input('region_id');
            $electionResult->constituency_id = $request->input('constituency_id');
            $electionResult->electoral_area_id	 = $request->input('electoral_area_id');


            $electionResult->election_type_id = $electionStartupDetail->election_type_id;
            $electionResult->election_start_up_id = $electionStartupDetail->id;
            $electionResult->obtained_votes = @$request->input('total1111');

            $electionResult->verify_by_constituency = 1;
            $electionResult->total_ballot = 0;
            $electionResult->total_rejected_ballot =$request->input('total_rejected_ballot');
            $electionResult->save();
        foreach ($posted_Data['party'] as $key => $value) {
                $partyElectionResult = new PartyElectionResult;
                $partyElectionResult->result_by_constituency = Auth::user()->id;
                $partyElectionResult->election_result_id = $electionResult->id;
                $partyElectionResult->polling_station_id = $electionResult->polling_station_id;

                $partyElectionResult->country_id = $electionResult->country_id;
                $partyElectionResult->region_id = $electionResult->region_id;
                $partyElectionResult->constituency_id = $electionResult->constituency_id;
                $partyElectionResult->electoral_area_id	 = $electionResult->electoralarea_id;

                $party_id = key($value);
                $partyElectionResult->party_id = key($value);
                foreach($value as $key => $_value){
                    $candidate_id = key($_value);
                    $partyElectionResult->candidate_id = key($_value);
                    foreach($_value as $key => $__value){
                       $obtained_vote =  $__value;
                       $partyElectionResult->obtained_vote = $__value;
                       $partyElectionResult->save();
                    }

                }
            $partyElectionResult->save();
            }
            $electionResult->obtained_votes = PartyElectionResult::where('election_result_id',$electionResult->id)
                //->where('user_id', Auth::user()->id)
                ->where('polling_station_id', $electionResult->polling_station_id)->sum('obtained_vote');

            $electionResult->save();
            $electionResult->total_ballot = $electionResult->obtained_votes +   $electionResult->total_rejected_ballot;
            $electionResult->save();
            $request->session()->flash('message', ' Election Result sent successfully!');
        }
        return redirect()->back();
    }
    public function election(){
        $electionStartupDetail = ElectionStartupDetail::select(
            'election_type.name',
            'election_startup_detail.*'
            )
        ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
        ->where("status",1)->get();

        $pollingStations = PollingStation::select(
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
        ->where('constituency.id',Auth::user()->constituency_id)->get();
        $pollingStations = PollingStation::where('constituency_id',Auth::user()->constituency_id)->get();
        return view("director.home.election",compact('pollingStations','electionStartupDetail'));
    }
    public function electionPost(Request $request){
            //dd($request->all());
           return redirect(route('Director.Home',[$request->input('election_start_update'),'null',$request->input('polling_station_id')]));
    }

    public function results(){

        $electionResults = ElectionResult::select(

            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_startup_detail.election_name",
            "election_result.obtained_votes",
            "election_result.id",
            "election_result.election_start_up_id",
            "PollingStation.total_voters"
        )
        ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        ->where('election_result.user_id',Auth::user()->id)

        //->where('election_result.election_start_up_id',1)
        ->get();
        //dd( $electionResults->toArray());
            return view('agent.home.results',compact('electionResults'));
    }
    public function viewResults($election_start_up,$election_result_id=false , Request $request)
    {

        $user = User::select(
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
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', Auth::user()->id)
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
            $request->session()->flash('error', ' Something went wrong!');

            return redirect(route("Agent.election"));
        }
       /*  $parties = PoliticalParty::select(
            'candidates.first_name',
            'candidates.last_name',
            'candidates.id as candidate_id',
            'political_party.party_initial',
            'political_party.id as political_party_id'
        )
        ->join('candidates','political_party.id','=','candidates.party_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)

        ->get(); */
        //edit

        $parties = PoliticalParty::select(
            'candidates.first_name',
            'candidates.last_name',
            'candidates.id as candidate_id',
            'political_party.party_initial',
            'political_party.id as political_party_id'
        )
        ->join('candidates','political_party.id','=','candidates.party_id');
        if($electionStartupDetail->election_type_id != 1){
            $parties = $parties->where('candidates.election_id',$electionStartupDetail->election_type_id);
            if($electionStartupDetail->election_type_id != 2 ){
                    $parties = $parties->where('candidates.polling_station_id',Auth::user()->polling_station_id);            }
            if($electionStartupDetail->election_type_id == 2){
                $parties = $parties->where('candidates.constituency_id',Auth::user()->constituency_id);
            }
        }
        if($electionStartupDetail->election_type_id == 1){
            $parties = $parties->whereNull('candidates.region_id');
            $parties = $parties->whereNull('candidates.constituency_id');
            $parties = $parties->whereNull('candidates.polling_station_id');
            $parties = $parties->whereNull('candidates.electoral_area_id');
        }
        $parties = $parties->get();

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
        ->where('election_result.user_id',Auth::user()->id)
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)

        ->where('election_result.election_start_up_id',$election_start_up);


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }
        $electionResult =$electionResult->get();



        return view('agent.home.viewResults',compact('election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
}
