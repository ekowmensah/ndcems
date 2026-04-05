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
use Illuminate\Support\Facades\DB;

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
            "pollingstation.name as PollingStation_name",
            "electoralarea.name as ElectoralArea_name",
            "pollingstation.polling_station_id as PollingStation_Id",
            "pollingstation.total_voters"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
        ->join('pollingstation','pollingstation.id','=','users.polling_station_id')->first();

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
            "election_result.pink_sheet_path",
            "election_result.verify_by_constituency"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->where('election_result.polling_station_id',Auth::user()->polling_station_id)
        ->where(function($query){
            $query->where('election_result.user_id', Auth::id())
                ->orWhereNull('election_result.user_id');
        })
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

        $existingElectionResult = ElectionResult::where('election_start_up_id', $election_start_up)
            ->where('polling_station_id', Auth::user()->polling_station_id)
            ->where(function($query){
                $query->where('user_id', Auth::id())
                    ->orWhereNull('user_id');
            });
        if($election_result_id){
            $existingElectionResult = $existingElectionResult->where('id', $election_result_id);
        }
        $existingElectionResult = $existingElectionResult->orderBy('id','desc')->first();
        if($existingElectionResult && is_null($existingElectionResult->user_id)){
            $existingElectionResult->user_id = Auth::id();
            $existingElectionResult->save();
            PartyElectionResult::where('election_result_id', $existingElectionResult->id)
                ->whereNull('user_id')
                ->update(['user_id' => Auth::id()]);
        }

        return view('agent.home.index',compact('existingElectionResult','election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
    public function captureResult($election_start_up,Request $request){
        $user = User::select(
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "pollingstation.name as PollingStation_name",
            "electoralarea.name as ElectoralArea_name",
            "pollingstation.polling_station_id as PollingStation_Id",
            "users.country_id"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
        ->join('pollingstation','pollingstation.id','=','users.polling_station_id')->first();

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

        $e_r = null;
        if($request->filled('election_result_id')){
            $e_r = ElectionResult::where('id',$request->input('election_result_id'))
                ->where('election_result.election_start_up_id',$election_start_up)
                ->where('election_result.polling_station_id', Auth::user()->polling_station_id)
                ->where(function($query){
                    $query->where('election_result.user_id', Auth::id())
                        ->orWhereNull('election_result.user_id');
                })
                ->first();
        }
        if(!$e_r){
            $e_r = ElectionResult::where('election_result.election_start_up_id',$election_start_up)
                ->where('election_result.polling_station_id', Auth::user()->polling_station_id)
                ->where(function($query){
                    $query->where('election_result.user_id', Auth::id())
                        ->orWhereNull('election_result.user_id');
                })
                ->orderBy('id','desc')
                ->first();
        }
        if($e_r && is_null($e_r->user_id)){
            $e_r->user_id = Auth::id();
            $e_r->save();
            PartyElectionResult::where('election_result_id', $e_r->id)
                ->whereNull('user_id')
                ->update(['user_id' => Auth::id()]);
        }
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
                $partyId = (int) key($value);
                foreach($value as $_value){
                    $candidateId = (int) key($_value);
                    $obtainedVote = (int) current($_value);
                    if($partyId <= 0 || $candidateId <= 0 || $obtainedVote < 0){
                        continue;
                    }

                    $partyElectionResult = PartyElectionResult::where('election_result_id',$e_r->id)
                        ->where('candidate_id', $candidateId)
                        ->where('party_id', $partyId)
                        ->where('polling_station_id', $e_r->polling_station_id)
                        ->where('user_id', Auth::user()->id)
                        ->first();
                    if(!$partyElectionResult){
                        $partyElectionResult = new PartyElectionResult;
                        $partyElectionResult->user_id = Auth::user()->id;
                        $partyElectionResult->election_result_id = $e_r->id;
                        $partyElectionResult->polling_station_id = $e_r->polling_station_id;
                        $partyElectionResult->country_id = $e_r->country_id;
                        $partyElectionResult->region_id = $e_r->region_id;
                        $partyElectionResult->constituency_id = $e_r->constituency_id;
                        $partyElectionResult->electoral_area_id = $e_r->electoral_area_id;
                        $partyElectionResult->party_id = $partyId;
                        $partyElectionResult->candidate_id = $candidateId;
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
    public function uploadPinkSheet($election_start_up, Request $request){
        $request->validate([
            'pink_sheet' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'election_result_id' => ['nullable', 'integer'],
        ]);

        $electionStartupDetail = ElectionStartupDetail::select('election_type.name','election_startup_detail.*')
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("election_startup_detail.id",$election_start_up)
            ->where("status",1)
            ->first();
        if(!$electionStartupDetail){
            $request->session()->flash('error', 'Something went wrong!');
            return redirect(route("Agent.election"));
        }

        $confirmedResult = ElectionResult::where('polling_station_id', Auth::user()->polling_station_id)
            ->where('election_start_up_id', $election_start_up)
            ->where('verify_by_constituency', 1)
            ->first();
        if($confirmedResult){
            $request->session()->flash('error', 'Sorry! Results have been confirmed by the Constituency Director. You cannot edit at this time.');
            return redirect(route("Agent.results"));
        }

        $electionResult = ElectionResult::where('election_start_up_id', $election_start_up)
            ->where('polling_station_id', Auth::user()->polling_station_id)
            ->where(function($query){
                $query->where('user_id', Auth::id())
                    ->orWhereNull('user_id');
            });
        if($request->filled('election_result_id')){
            $electionResult = $electionResult->where('id', (int) $request->input('election_result_id'));
        }
        $electionResult = $electionResult->orderBy('id','desc')->first();
        if(!$electionResult){
            $electionResult = ElectionResult::where('election_start_up_id', $election_start_up)
                ->where('polling_station_id', Auth::user()->polling_station_id)
                ->where(function($query){
                    $query->where('user_id', Auth::id())
                        ->orWhereNull('user_id');
                })
                ->orderBy('id','desc')
                ->first();
        }
        if($electionResult && is_null($electionResult->user_id)){
            $electionResult->user_id = Auth::id();
            $electionResult->save();
            PartyElectionResult::where('election_result_id', $electionResult->id)
                ->whereNull('user_id')
                ->update(['user_id' => Auth::id()]);
        }

        if(!$electionResult){
            $electionResult = new ElectionResult;
            $electionResult->polling_station_id =  Auth::user()->polling_station_id;
            $electionResult->user_id = Auth::user()->id;
            $electionResult->user_type_id = Auth::user()->user_type_id;
            $electionResult->country_id = Auth::user()->country_id;
            $electionResult->region_id = Auth::user()->region_id;
            $electionResult->constituency_id = Auth::user()->constituency_id;
            $electionResult->electoral_area_id = Auth::user()->electoralarea_id;
            $electionResult->election_type_id = $electionStartupDetail->election_type_id;
            $electionResult->election_start_up_id = $electionStartupDetail->id;
            $electionResult->obtained_votes = 0;
            $electionResult->total_ballot = 0;
            $electionResult->total_rejected_ballot = 0;
        }
        $isNewElectionResult = !$electionResult->exists;
        if($isNewElectionResult){
            $electionResult->save();

            $partyCandidates = PoliticalParty::select(
                    'candidates.id as candidate_id',
                    'political_party.id as political_party_id'
                )
                ->join('candidates','political_party.id','=','candidates.party_id')
                ->orderBy('candidates.ordering_position','ASC');
            if($electionStartupDetail->election_type_id != 1){
                $partyCandidates = $partyCandidates->where('candidates.election_id',$electionStartupDetail->election_type_id);
                if($electionStartupDetail->election_type_id != 2){
                    $partyCandidates = $partyCandidates->where('candidates.polling_station_id',Auth::user()->polling_station_id);
                }
                if($electionStartupDetail->election_type_id == 2){
                    $partyCandidates = $partyCandidates->where('candidates.constituency_id',Auth::user()->constituency_id);
                }
            }
            if($electionStartupDetail->election_type_id == 1){
                $partyCandidates = $partyCandidates->whereNull('candidates.region_id');
                $partyCandidates = $partyCandidates->whereNull('candidates.constituency_id');
                $partyCandidates = $partyCandidates->whereNull('candidates.polling_station_id');
                $partyCandidates = $partyCandidates->whereNull('candidates.electoral_area_id');
            }
            $partyCandidates = $partyCandidates
                ->where('candidates.election_start_up_id',$electionStartupDetail->id)
                ->get();

            foreach ($partyCandidates as $partyCandidate){
                PartyElectionResult::create([
                    'user_id' => Auth::user()->id,
                    'election_result_id' => $electionResult->id,
                    'polling_station_id' => $electionResult->polling_station_id,
                    'country_id' => $electionResult->country_id,
                    'region_id' => $electionResult->region_id,
                    'constituency_id' => $electionResult->constituency_id,
                    'electoral_area_id' => $electionResult->electoral_area_id,
                    'party_id' => $partyCandidate->political_party_id,
                    'candidate_id' => $partyCandidate->candidate_id,
                    'obtained_vote' => 0,
                ]);
            }
        }

        $pinkSheetDirectory = storage_path('app/pink_sheets');
        if(!is_dir($pinkSheetDirectory)){
            mkdir($pinkSheetDirectory, 0755, true);
        }

        $file = $request->file('pink_sheet');
        $filename = 'pink_sheet_'.$electionResult->id.'_'.time().'.'.$file->getClientOriginalExtension();
        $file->move($pinkSheetDirectory, $filename);

        $previousPinkSheet = $electionResult->pink_sheet_path;
        $electionResult->pink_sheet_path = $filename;
        $electionResult->save();

        if($previousPinkSheet){
            $previousStoragePath = $pinkSheetDirectory.DIRECTORY_SEPARATOR.$previousPinkSheet;
            if(file_exists($previousStoragePath)){
                @unlink($previousStoragePath);
            }
            $previousPublicPath = public_path('pink_sheets').DIRECTORY_SEPARATOR.$previousPinkSheet;
            if(file_exists($previousPublicPath)){
                @unlink($previousPublicPath);
            }
        }

        $request->session()->flash('message', 'Pink sheet uploaded successfully.');
        return redirect()->back();
    }
    public function viewPinkSheet($election_result_id){
        $electionResult = ElectionResult::where('id', $election_result_id)
            ->where('polling_station_id', Auth::user()->polling_station_id)
            ->where(function($query){
                $query->where('user_id', Auth::id())
                    ->orWhereNull('user_id');
            })
            ->first();
        if(!$electionResult){
            abort(403);
        }
        if(!$electionResult->pink_sheet_path){
            abort(404);
        }

        $path = $this->resolvePinkSheetPath($electionResult->pink_sheet_path);
        if(!$path){
            abort(404);
        }

        return response()->file($path, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
    private function resolvePinkSheetPath(string $filename){
        $storagePath = storage_path('app/pink_sheets').DIRECTORY_SEPARATOR.$filename;
        if(file_exists($storagePath)){
            return $storagePath;
        }
        $legacyPublicPath = public_path('pink_sheets').DIRECTORY_SEPARATOR.$filename;
        if(file_exists($legacyPublicPath)){
            return $legacyPublicPath;
        }
        return false;
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

        $latestResultIds = ElectionResult::select(DB::raw('MAX(id) as id'))
            ->where('polling_station_id', Auth::user()->polling_station_id)
            ->where('user_id', Auth::id())
            ->groupBy('election_start_up_id');

        $electionResults = ElectionResult::select(
                "election_result.total_ballot",
                "election_result.total_rejected_ballot",
                "election_result.election_start_up_id",
                "election_startup_detail.election_name",
                "election_result.obtained_votes",
                "election_result.id",
                "election_result.result_by_constituency",
                "election_result.verify_by_constituency",
                "election_result.pink_sheet_path",
                "election_result.election_start_up_id",
                "pollingstation.total_voters",
                "election_type.name as election_type_name"
            )
            ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')
            ->join('pollingstation','pollingstation.id','=','election_result.polling_station_id')
            ->join('election_type','election_result.election_type_id','=','election_type.id')
            ->whereIn('election_result.id', $latestResultIds)
            ->orderBy('election_result.id','desc')
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
            "pollingstation.name as PollingStation_name",
            "electoralarea.name as ElectoralArea_name",
            "pollingstation.polling_station_id as PollingStation_Id"
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->join('region','region.id','=','users.region_id')
        ->join('constituency','constituency.id','=','users.constituency_id')
        ->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
        ->join('pollingstation','pollingstation.id','=','users.polling_station_id')->first();

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
            "election_result.pink_sheet_path",
            "pollingstation.total_voters"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
//        ->where('election_result.user_id',Auth::user()->id)
        ->join('pollingstation','pollingstation.id','=','election_result.polling_station_id')
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

