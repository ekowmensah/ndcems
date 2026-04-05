<?php

namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Model\ElectionStartupDetail;
use App\Model\PoliticalParty;
use App\Model\ElectionResult;
use App\Model\PartyElectionResult;
use App\Model\ElectionType;

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
    public function index($election_start_up, Request $request, $election_result_id=false)
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
            "PollingStation.polling_station_id as PollingStation_Id",
            "PollingStation.total_voters"
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

        // Block access if results for this polling station + election have been confirmed by director
        $confirmedResult = ElectionResult::where('polling_station_id', Auth::user()->polling_station_id)
            ->where('election_start_up_id', $election_start_up)
            ->where('verify_by_constituency', 1)
            ->first();
        if($confirmedResult){
            $request->session()->flash('error', 'Sorry! Results have been confirmed by the Constituency Director. You cannot edit at this time.');
            return redirect(route("Agent.results"));
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
        //  dd($parties->toArray());

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
            "election_result.verify_by_constituency"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->where('election_result.polling_station_id',Auth::user()->polling_station_id)
        //->where('candidates.election_id',$electionStartupDetail->election_type_id)
        ->where('candidates.election_start_up_id',$electionStartupDetail->id)

        //->where('election_result.user_id',Auth::user()->id)
        ->where('election_result.election_start_up_id',$election_start_up)
        ->orderBy('candidates.ordering_position','ASC');


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }
        $electionResult =$electionResult->get();
        $checkIsEditAble =$electionResult->first();

        if(isset($checkIsEditAble->verify_by_constituency) && $checkIsEditAble->verify_by_constituency==1){
             $request->session()->flash('error', 'Sorry! Results Confirmed by Director. You cannot Edit at this Time.');
              return redirect(route("Agent.election"));
        }

        return view('agent.home.index',compact('election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
    public function captureResult($election_start_up,Request $request){
        $user = User::select(
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
        ->join('PollingStation','PollingStation.id','=','users.polling_station_id')->first();

        $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
        )
        ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
        ->where("election_startup_detail.id",$election_start_up)

        ->where("status",1)->first();
        if(!$electionStartupDetail){
            $request->session()->flash('error', 'Something went wrong!');
            return redirect(route("Agent.election"));
        }
        $posted_Data = $request->all();
        if(!isset($posted_Data['party']) || !is_array($posted_Data['party'])){
            $request->session()->flash('error', 'No party vote data was submitted.');
            return redirect()->back();
        }
        $totalRejectedBallot = (int) $request->input('total_rejected_ballot', 0);
        if($totalRejectedBallot < 0){
            $request->session()->flash('error', 'Rejected ballot count must be zero or greater.');
            return redirect()->back();
        }

        // Block saving if results have been confirmed by director
        $confirmedResult = ElectionResult::where('polling_station_id', Auth::user()->polling_station_id)
            ->where('election_start_up_id', $election_start_up)
            ->where('verify_by_constituency', 1)
            ->first();
        if($confirmedResult){
            $request->session()->flash('error', 'Sorry! Results have been confirmed by the Constituency Director. You cannot edit at this time.');
            return redirect(route("Agent.results"));
        }

        $e_r = ElectionResult::where('id',$request->input('election_result_id'))
            ->where('election_result.election_start_up_id',$election_start_up)
            ->where('election_result.polling_station_id', Auth::user()->polling_station_id)
            ->where('election_result.user_id', Auth::user()->id)
            ->first();
       //dd( $e_r->toArray());
        if($e_r){
            // Double-check this specific result isn't confirmed
            if($e_r->verify_by_constituency == 1){
                $request->session()->flash('error', 'Sorry! Results have been confirmed by the Constituency Director. You cannot edit at this time.');
                return redirect(route("Agent.results"));
            }
            $e_r->total_ballot =0;
            $e_r->total_rejected_ballot = $totalRejectedBallot;
            $e_r->save();

            foreach ($posted_Data['party'] as $key => $value) {
                foreach($value as $_value){
                    $candidateId = (int) key($_value);
                    $obtainedVote = (int) current($_value);
                    if($candidateId <= 0 || $obtainedVote < 0){
                        continue;
                    }

                    $partyElectionResult = PartyElectionResult::where('election_result_id',$e_r->id)
                        ->where('candidate_id', $candidateId)
                        ->where('polling_station_id', $e_r->polling_station_id)
                        ->where('user_id', Auth::user()->id)
                        ->first();
                    if(!$partyElectionResult){
                        continue;
                    }
                    $partyElectionResult->obtained_vote = $obtainedVote;
                    $partyElectionResult->save();
                }
            }
            $e_r->obtained_votes = PartyElectionResult::where('election_result_id',$e_r->id)
                ->where('user_id', Auth::user()->id)
                ->where('polling_station_id', $e_r->polling_station_id)->sum('obtained_vote');
            $e_r->save();
            $e_r->total_ballot = $e_r->obtained_votes +   $e_r->total_rejected_ballot;
            $e_r->save();
            $request->session()->flash('message', ' Election Result updated successfully!');


        }else{

            $electionResult = new ElectionResult;
            $electionResult->polling_station_id =  Auth::user()->polling_station_id;
            $electionResult->user_id = Auth::user()->id;
            $electionResult->user_type_id = Auth::user()->user_type_id;
            $electionResult->country_id = Auth::user()->country_id;
            $electionResult->region_id = Auth::user()->region_id;
            $electionResult->constituency_id = Auth::user()->constituency_id;
            $electionResult->electoral_area_id	 = Auth::user()->electoralarea_id;


            $electionResult->election_type_id = $electionStartupDetail->election_type_id;
            $electionResult->election_start_up_id = $electionStartupDetail->id;
            $electionResult->obtained_votes = 0;

            $electionResult->total_ballot = 0;
            $electionResult->total_rejected_ballot = $totalRejectedBallot;
            $electionResult->save();
        foreach ($posted_Data['party'] as $key => $value) {
                $partyId = (int) key($value);
                foreach($value as $_value){
                    $candidateId = (int) key($_value);
                    $obtainedVote = (int) current($_value);
                    if($partyId <= 0 || $candidateId <= 0 || $obtainedVote < 0){
                        continue;
                    }

                    $partyElectionResult = new PartyElectionResult;
                    $partyElectionResult->user_id = Auth::user()->id;
                    $partyElectionResult->election_result_id = $electionResult->id;
                    $partyElectionResult->polling_station_id = $electionResult->polling_station_id;
                    $partyElectionResult->country_id = $electionResult->country_id;
                    $partyElectionResult->region_id = $electionResult->region_id;
                    $partyElectionResult->constituency_id = $electionResult->constituency_id;
                    $partyElectionResult->electoral_area_id	 = $electionResult->electoral_area_id;
                    $partyElectionResult->party_id = $partyId;
                    $partyElectionResult->candidate_id = $candidateId;
                    $partyElectionResult->obtained_vote = $obtainedVote;
                    $partyElectionResult->save();
                }
            }
            $electionResult->obtained_votes = PartyElectionResult::where('election_result_id',$electionResult->id)
                ->where('user_id', Auth::user()->id)
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

        return view("agent.home.election",compact('electionStartupDetail'));
    }
    public function electionPost(Request $request){
           return redirect(route('Agent.Home',$request->input('election_start_update')));
    }

    public function results(){

        $electionResults = ElectionResult::select(

            "election_result.total_ballot",
            "election_result.total_rejected_ballot",
            "election_result.election_start_up_id",
            "election_startup_detail.election_name",
            "election_result.obtained_votes",
            "election_result.id",
            "election_result.result_by_constituency",
            "election_result.verify_by_constituency",
            "election_result.election_start_up_id",
            "PollingStation.total_voters",
            "election_type.name as election_type_name"
        )
        ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        ->join('election_type','election_result.election_type_id','=','election_type.id')
//        ->where('election_result.user_id',Auth::user()->id)
        ->where('election_result.polling_station_id',Auth::user()->polling_station_id)

        //->where('election_result.election_start_up_id',1)
        ->get();
       // dd( $electionResults->toArray());
            return view('agent.home.results',compact('electionResults'));
    }
    public function viewResults($election_start_up, Request $request, $election_result_id=false)
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
        ->join('candidates','political_party.id','=','candidates.party_id')
        ->orderBy('candidates.ordering_position','ASC');
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
//        ->where('election_result.user_id',Auth::user()->id)
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)

        ->where('election_result.election_start_up_id',$election_start_up)
        ->where('election_result.polling_station_id',Auth::user()->polling_station_id)
        ->orderBy('candidates.ordering_position','ASC');


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }
        $electionResult =$electionResult->get();



        return view('agent.home.viewResults',compact('election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
}
