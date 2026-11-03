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
use App\Model\ResultApprovalAudit;
use App\Model\ElectionType;
use App\Model\UserType;
use DataTables;
use Validator;
use App\Model\Region;
use App\Model\Constituency;
use App\Model\ElectoralArea;
use App\Model\PollingStation;
use App\Model\Country;
use Illuminate\Support\Facades\Hash;
use DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Services\Results\ConstituencyResultApprovalService;


class PollingAgentController extends Controller
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
    public function index(){
        return view("director.home.index");
    }
    public function pollingAgent(){
        $UserType = UserType::latest()->first();
        return view("director.polling.index",compact('UserType'));
    }
    public function pollingAgentAjax(Request $request){
        $UserType = UserType::latest()->first();
        $Users = User::select(
                'users.username',
                'users.created_at',
                'users.name as user_name',
                'users.id as user_id',
                'user_type.id as user_type_id',
                'user_type.name as user_type_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "pollingstation.name as PollingStation_name",
                "electoralarea.name as ElectoralArea_name"
            )
            ->where('user_type.id', $UserType->id)
            ->where('constituency.id', Auth::user()->constituency_id)
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->join('region','region.id','=','users.region_id')
            ->join('constituency','constituency.id','=','users.constituency_id')
            ->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
            ->join('pollingstation','pollingstation.id','=','users.polling_station_id');

        //->leftJoin('pollingstation','pollingstation.electoralarea_id','=','electoralarea.id');
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
    public function UsersEdit($id){
        $User = User::find($id);

        $Type = UserType::find($User->user_type_id);
        //user type config

        $countries = Country::all();
        $UserTypes = UserType::where('id','<=',$Type->id)->get();
        $NewUserTypes = [];
        foreach ($UserTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewUserTypes[] = array_merge($value,array_merge($value,$data));

          }

       // $belongTo = UserType::where('id',$Type->parent)->get();
        $belongTo = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id',$Type->parent)
            ->get();

        $regions = Region::where('country_id',$User->country_id)->get();
        $constituency = Constituency::where('region_id',$User->region_id)->get();
        $electoralarea = ElectoralArea::where('constituency_id',$User->constituency_id)->get();
        $pollingstation = PollingStation::where('electoralarea_id',$User->electoralarea_id)->get();

        return view('director.polling.edit_polling_agent',compact('pollingstation','electoralarea','constituency','regions','countries','UserTypes','Type','belongTo','User','NewUserTypes'));
    }
    public function EditUserPost(Request $request){
        $validation =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'username' => ['required', 'string', 'min:10', 'max:10'],
            // /'password' => ['string', 'min:6'],

            'user_type_id' => ['required'],
            'phoneno' => ['required', 'string', 'min:7'],
            //'constituency' => ['required'],
            'gender' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()
                        ->withErrors($validation)
                        ->withInput();
        }else{
            $data = $request->all();
            $user = User::find($data['id']);
            $user->name =  $data['name'];
            $user->email =  $data['email'];
            $user->username =  $data['username'];
            $user->phoneno =  $data['phoneno'];
            //$user->country_id =  $data['country_id'];
            $user->gender =  $data['gender'];

            /* if(isset($data['region_id']))
                $user->region_id =  $data['region_id'];
            if(isset($data['constituency_id']))
                $user->constituency_id =  $data['constituency_id']; */
            if(isset($data['electoralarea_id']))
                 $user->electoralarea_id =  $data['electoralarea_id'];
            if(isset($data['polling_station_id']))
                $user->polling_station_id =  $data['polling_station_id'];

            if($data['password'])
            {
                $user->password =   Hash::make($data['password']);
                $UserType = UserType::latest()->first();
                if($data['user_type_id'] == $UserType->id){
                    $user->secret = Hash::make($request->input('password'));
                }
            }
            $user->save();



            $request->session()->flash('message', ' User Updated Successfully!');
            return redirect()->back();
        }

    }
    public function VerifyUsername(Request $request){
        $data = $request->all();
        $username =  User::where('username',$data['username'])->first();
        if($username)
            return "faund";
        else
            return "not_found";
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
    public function UsersDelete($id,Request $request){
        $user = User::where('id', $id)
            ->where('user_type_id', User::TYPE_POLLING_AGENT)
            ->where('constituency_id', Auth::user()->constituency_id)
            ->firstOrFail();
        $user->delete();
        $request->session()->flash('message', 'Polling agent archived successfully.');
        return redirect()->back();
    }
    public function newUser($type_id){
        $UserType = UserType::latest()->first();

        $Type = UserType::find($type_id);
        $under = UserType::where('id',$Type->parent);

        $countries = Country::all();
        $UserTypes = UserType::where('id','<=',$Type->id)->get();
        $NewUserTypes = [];
        foreach ($UserTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewUserTypes[] = array_merge($value,array_merge($value,$data));
        }
       // $belongTo = UserType::where('id',$Type->parent)->get();
        $belongTo = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id',$Type->parent)
            ->get();
        //dd($belongTo->toArray());
        $User = Auth::user();
        $regions = Region::where('country_id',$User->country_id)->get();
        $constituency = Constituency::where('region_id',$User->region_id)->get();
        $electoralarea = ElectoralArea::where('constituency_id',$User->constituency_id)->get();
        $pollingstation = PollingStation::where('electoralarea_id',$User->electoralarea_id)->get();

        return view('director.polling.User',compact('User','pollingstation','electoralarea','constituency','regions','UserType','countries','UserTypes','Type','belongTo','under','NewUserTypes'));
    }
    public function newUserPost(Request $request){
        $validation =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'min:10', 'max:10', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],

            'user_type_id' => ['required'],
            'phoneno' => ['required', 'string', 'min:7'],
            //'constituency' => ['required'],
            'gender' => ['required', 'string'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()
                        ->withErrors($validation)
                        ->withInput();
        }else{
            $data = $request->all();
            $User = Auth::user();
            $data['country_id'] = $User->country_id;
            $data['region_id'] = $User->region_id;
            $data['constituency_id'] = $User->constituency_id;
            $data['password'] = Hash::make($data['password']);
            $UserType = UserType::latest()->first();
            if($data['user_type_id'] == $UserType->id){
                $data['secret'] = Hash::make($request->input('password'));
            }


            User::create($data);
            $request->session()->flash('message', ' User Created Successfully!');
            return redirect(route('Director.pollingAgent'));
        }


    }
    public function result(){
        $election = ElectionType::all();
        $constituencyId = Auth::user()->constituency_id;
        $analytics = $this->buildResultAnalytics($constituencyId, 'all');
        return view('director.polling.result', compact('election') + $analytics);
    }

    public function resultAnalytics(Request $request)
    {
        $constituencyId = Auth::user()->constituency_id;
        $electionTypeId = $request->input('election_type_id', 'all');
        $analytics = $this->buildResultAnalytics($constituencyId, $electionTypeId);
        return response()->json($analytics);
    }

    private function buildResultAnalytics($constituencyId, $electionTypeId = 'all')
    {
        $baseQuery = ElectionResult::where('election_result.constituency_id', $constituencyId);
        if ($electionTypeId !== 'all') {
            $baseQuery->where('election_result.election_type_id', (int) $electionTypeId);
        }

        $summary = (clone $baseQuery)->selectRaw('
            COUNT(*) as submitted,
            SUM(CASE WHEN approval_status = "approved" THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN approval_status = "pending" THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN approval_status = "rejected" THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN pink_sheet_path IS NOT NULL AND pink_sheet_path != "" THEN 1 ELSE 0 END) as pink_sheets,
            SUM(obtained_votes) as total_valid_votes,
            SUM(total_rejected_ballot) as total_rejected_votes
        ')->first();

        $totalPollingStations = PollingStation::where('constituency_id', $constituencyId)->count();
        $submitted = (int) ($summary->submitted ?? 0);
        $confirmed = (int) ($summary->confirmed ?? 0);
        $pending = (int) ($summary->pending ?? 0);
        $rejected = (int) ($summary->rejected ?? 0);
        $pinkSheets = (int) ($summary->pink_sheets ?? 0);

        $overVoting = (clone $baseQuery)
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->whereRaw('CAST(election_result.total_ballot AS UNSIGNED) > CAST(pollingstation.total_voters AS UNSIGNED)')
            ->count();

        $resultSummary = [
            'submitted' => $submitted,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'rejected' => $rejected,
            'pink_sheets' => $pinkSheets,
            'missing_pink_sheets' => max($submitted - $pinkSheets, 0),
            'total_polling_stations' => $totalPollingStations,
            'coverage_rate' => $totalPollingStations > 0 ? round(($submitted / $totalPollingStations) * 100, 2) : 0,
            'confirmation_rate' => $submitted > 0 ? round(($confirmed / $submitted) * 100, 2) : 0,
            'pink_sheet_rate' => $submitted > 0 ? round(($pinkSheets / $submitted) * 100, 2) : 0,
            'total_valid_votes' => (int) ($summary->total_valid_votes ?? 0),
            'total_rejected_votes' => (int) ($summary->total_rejected_votes ?? 0),
            'over_voting_cases' => (int) $overVoting,
        ];

        $statusPie = [
            'labels' => ['Approved', 'Pending', 'Rejected'],
            'values' => [$confirmed, $pending, $rejected],
        ];

        $pinkSheetPie = [
            'labels' => ['With Pink Sheet', 'Missing Pink Sheet'],
            'values' => [$pinkSheets, max($submitted - $pinkSheets, 0)],
        ];

        $electionTypeRows = ElectionResult::select(
                'election_type.name as election_type_name',
                DB::raw('COUNT(election_result.id) as submissions'),
                DB::raw('SUM(CASE WHEN election_result.approval_status = "approved" THEN 1 ELSE 0 END) as confirmations')
            )
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->where('election_result.constituency_id', $constituencyId)
            ->groupBy('election_type.id', 'election_type.name')
            ->orderBy('election_type.name', 'asc')
            ->get();

        $electionTypeBar = [
            'labels' => $electionTypeRows->pluck('election_type_name')->values(),
            'submissions' => $electionTypeRows->pluck('submissions')->map(function ($value) {
                return (int) $value;
            })->values(),
            'confirmations' => $electionTypeRows->pluck('confirmations')->map(function ($value) {
                return (int) $value;
            })->values(),
        ];

        $partyVotesQuery = PartyElectionResult::select(
                'political_party.party_initial',
                DB::raw('SUM(party_election_result.obtained_vote) as total_votes')
            )
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->where('party_election_result.constituency_id', $constituencyId);
        if ($electionTypeId !== 'all') {
            $partyVotesQuery->join('election_result', 'election_result.id', '=', 'party_election_result.election_result_id')
                ->where('election_result.election_type_id', (int) $electionTypeId);
        }
        $partyVotes = $partyVotesQuery
            ->groupBy('political_party.id', 'political_party.party_initial')
            ->orderByDesc('total_votes')
            ->limit(8)
            ->get();

        $partyVotePie = [
            'labels' => $partyVotes->pluck('party_initial')->values(),
            'values' => $partyVotes->pluck('total_votes')->map(function ($value) {
                return (int) $value;
            })->values(),
        ];

        $hourSlots = collect(range(23, 1))->map(function ($offset) {
            return Carbon::now()->subHours($offset)->format('Y-m-d H:00:00');
        })->push(Carbon::now()->format('Y-m-d H:00:00'));

        $hourlyRows = (clone $baseQuery)
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour_slot'),
                DB::raw('COUNT(id) as submissions')
            )
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00")'))
            ->orderBy('hour_slot', 'asc')
            ->get()
            ->keyBy('hour_slot');

        $trendHourly = [
            'labels' => [],
            'submissions' => [],
        ];
        foreach ($hourSlots as $slot) {
            $row = $hourlyRows->get($slot);
            $trendHourly['labels'][] = Carbon::parse($slot)->format('H:i');
            $trendHourly['submissions'][] = (int) optional($row)->submissions;
        }

        return [
            'resultSummary' => $resultSummary,
            'statusPie' => $statusPie,
            'pinkSheetPie' => $pinkSheetPie,
            'electionTypeBar' => $electionTypeBar,
            'partyVotePie' => $partyVotePie,
            'trendHourly' => $trendHourly,
        ];
    }
    public function pollingStationResultAajax(Request $request ){
        $regions = PollingStation::select(
            'pollingstation.*',
            'pollingstation.total_voters',
            'election_result.obtained_votes',
            'election_result.total_ballot',
            'election_result.user_id as election_result_user_id',
            'election_result.total_rejected_ballot',
            'election_result.pink_sheet_path',
            'election_result.id as election_result_id',
            'election_result.election_start_up_id',
            "election_result.verify_by_constituency",
            "election_result.approval_status",
            "election_result.approved_at",
            "election_result.rejection_reason",
            "election_result.verify_by_regional",
            "election_result.result_by_constituency",
            'election_startup_detail.election_name',
            'election_type.name as election_type_name',
            'users.name as agent_name',
            'users.phoneno as agent_phoneno'
        )
        ->join('election_result','election_result.polling_station_id','=','pollingstation.id')
        ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')
        ->join('election_type','election_result.election_type_id','=','election_type.id')
        //->join("users","users.constituency_id","=","election_result.constituency_id")
        //->where('election_result.constituency_id',Auth::user()->constituency_id)
        ->leftJoin('users','users.id','=','election_result.result_by_constituency')

        ;
        //->distinct('pollingstation.id')

        //->leftJoin('users','users.id','=','election_result.result_by_constituency')

        /* ->join("users",function($join){
            $join//->on("users.constituency_id","=","election_result.constituency_id");
                ->on("users.id","=","election_result.result_by_constituency")
                ->orWhere('election_result.result_by_constituency', '=', Auth::user()->id);
        })
        ->get(); */

        //->leftJoin('pollingstation','pollingstation.constituency_id','=','constituency.id');
        //if($request->input('region_id') != 'all')
        if($request->input('election_type_id')!="all"){
            $regions = $regions->where('election_type.id',$request->input('election_type_id'));

        }
        $regions = $regions ->where('election_result.constituency_id',Auth::user()->constituency_id);
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
        ->where('election_result.constituency_id',$id)
        ->orderBy('candidates.ordering_position','ASC');
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
        return view("region.home.presidentialResultView",compact('constituency_detail','dataPoints'));
    }
    public function viewResults($election_start_up, Request $request, $election_result_id=false)
    {
        $resultId = $election_result_id ?: $election_start_up;
        /* if(!$electionStartupDetail){
            $request->session()->flash('error', ' Something went wrong!');

            return redirect(route("Director.election"));
        } */
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

        /* $parties = PoliticalParty::select(
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
        $parties = $parties->get(); */
        $parties = collect();

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
            "pollingstation.total_voters",
            "pollingstation.name as PollingStation_name",
            "pollingstation.polling_station_id as PollingStation_Id",
            "electoralarea.name as ElectoralArea_name",
            "constituency.name as constituency_name",
            "region.name as region_name",
            "election_result.verify_by_constituency",
            "election_result.verify_by_regional",
            "election_result.pink_sheet_path"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('election_result.user_id',Auth::user()->id)
        ->join('pollingstation','pollingstation.id','=','election_result.polling_station_id')
        ->join('electoralarea','electoralarea.id','=','election_result.electoral_area_id')
        ->join('constituency','constituency.id','=','election_result.constituency_id')
        ->join('region','region.id','=','election_result.region_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        //->where('candidates.election_id',$electionStartupDetail->election_type_id)
        ->orderBy('candidates.ordering_position','ASC')
        ->where('election_result.constituency_id', Auth::user()->constituency_id)
        ->where('election_result.id',$resultId);
        //->orderBy('candidates.ordering_position','ASC');
            //dd($electionResult);

        /* if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        } */
        $electionResult =$electionResult->get();
        if($electionResult->isEmpty()){
            $request->session()->flash('error', 'Result not found for this constituency.');
            return redirect(route('Director.Result'));
        }

        $resultMeta = $electionResult->first();
        $electionStartupDetail = ElectionStartupDetail::select('election_type.name','election_startup_detail.*')
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("election_startup_detail.id",$resultMeta->election_start_up_id)
            ->first();

        $user = (object) [
            'PollingStation_name' => $resultMeta->PollingStation_name,
            'PollingStation_Id' => $resultMeta->PollingStation_Id,
            'ElectoralArea_name' => $resultMeta->ElectoralArea_name,
            'constituency_name' => $resultMeta->constituency_name,
            'region_name' => $resultMeta->region_name,
            'user_type_name' => 'Constituency Director',
        ];

        //dd($electionResult);

        return view('director.polling.viewResults',compact('election_start_up','electionResult','parties','user','electionStartupDetail'));
        //return view('director.polling.viewResults');

    }
    public function viewPinkSheet($election_result_id){
        $electionResult = ElectionResult::where('id', $election_result_id)
            ->where('constituency_id', Auth::user()->constituency_id)
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
    public function resultDetail($id)
    {
        $resultMeta = ElectionResult::select(
                'election_result.id',
                'election_result.total_ballot',
                'election_result.total_rejected_ballot',
                'election_result.obtained_votes',
                'election_result.pink_sheet_path',
                'election_result.verify_by_constituency',
                'election_result.approval_status',
                'election_result.approved_at',
                'election_result.rejection_reason',
                'election_startup_detail.election_name',
                'election_type.name as election_type_name',
                'pollingstation.name as polling_station_name',
                'pollingstation.polling_station_id as polling_station_code',
                'pollingstation.total_voters'
            )
            ->join('election_startup_detail', 'election_startup_detail.id', '=', 'election_result.election_start_up_id')
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->where('election_result.id', $id)
            ->where('election_result.constituency_id', Auth::user()->constituency_id)
            ->first();

        if(!$resultMeta){
            return response()->json(['message' => 'Result not found.'], 404);
        }

        $candidates = PartyElectionResult::select(
                'candidates.first_name',
                'candidates.last_name',
                'political_party.party_initial',
                'party_election_result.obtained_vote'
            )
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->where('party_election_result.election_result_id', $resultMeta->id)
            ->orderBy('candidates.ordering_position', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'candidate_name' => trim($item->first_name . ' ' . $item->last_name),
                    'party_initial' => $item->party_initial,
                    'votes' => (int) $item->obtained_vote,
                ];
            })
            ->values();

        $approvalHistory = ResultApprovalAudit::query()
            ->select('result_approval_audits.*', 'users.name as actor_name')
            ->leftJoin('users', 'users.id', '=', 'result_approval_audits.actor_id')
            ->where('result_approval_audits.election_result_id', $resultMeta->id)
            ->orderByDesc('result_approval_audits.id')
            ->get()
            ->map(function ($audit) {
                return [
                    'action' => $audit->action,
                    'from_status' => $audit->from_status,
                    'to_status' => $audit->to_status,
                    'reason' => $audit->reason,
                    'actor_name' => $audit->actor_name ?: 'Constituency Director',
                    'created_at' => optional($audit->created_at)->format('M d, Y g:i A'),
                ];
            })->values();

        return response()->json([
            'id' => (int) $resultMeta->id,
            'election_name' => $resultMeta->election_name,
            'election_type_name' => $resultMeta->election_type_name,
            'polling_station_name' => $resultMeta->polling_station_name,
            'polling_station_code' => $resultMeta->polling_station_code,
            'total_voters' => (int) $resultMeta->total_voters,
            'obtained_votes' => (int) $resultMeta->obtained_votes,
            'total_rejected_ballot' => (int) $resultMeta->total_rejected_ballot,
            'total_ballot' => (int) $resultMeta->total_ballot,
            'verify_by_constituency' => (int) $resultMeta->verify_by_constituency,
            'approval_status' => $resultMeta->approval_status ?: ((int) $resultMeta->verify_by_constituency === 1 ? 'approved' : 'pending'),
            'approved_at' => $resultMeta->approved_at,
            'rejection_reason' => $resultMeta->rejection_reason,
            'has_pink_sheet' => !empty($resultMeta->pink_sheet_path),
            'pink_sheet_view_url' => !empty($resultMeta->pink_sheet_path) ? route('Director.ViewPinkSheet', $resultMeta->id) : null,
            'pink_sheet_download_url' => !empty($resultMeta->pink_sheet_path) ? route('Director.DownloadPinkSheet', $resultMeta->id) : null,
            'candidates' => $candidates,
            'approval_history' => $approvalHistory,
        ]);
    }
    public function downloadPinkSheet($election_result_id){
        $electionResult = ElectionResult::where('id', $election_result_id)
            ->where('constituency_id', Auth::user()->constituency_id)
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

        $pollingStation = PollingStation::find($electionResult->polling_station_id);
        $stationName = $this->sanitizeFilenamePart(optional($pollingStation)->name ?? 'polling_station');
        $stationCode = $this->sanitizeFilenamePart(optional($pollingStation)->polling_station_id ?? 'code');
        $extension = pathinfo($electionResult->pink_sheet_path, PATHINFO_EXTENSION);
        if(!$extension){
            $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        }
        $downloadFilename = $stationName.'_'.$stationCode.'.'.$extension;

        return response()->download($path, $downloadFilename, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
    public function uploadPinkSheet($election_result_id, Request $request){
        $request->validate([
            'pink_sheet' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $electionResult = ElectionResult::where('id', $election_result_id)
            ->where('constituency_id', Auth::user()->constituency_id)
            ->first();
        if(!$electionResult){
            abort(403);
        }
        if((int) $electionResult->verify_by_constituency === 1){
            $request->session()->flash('error', 'Approved results are locked. Reopen the result before replacing its pink sheet.');
            return redirect()->back();
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
        if($electionResult->approval_status === ConstituencyResultApprovalService::STATUS_REJECTED){
            $electionResult->approval_status = ConstituencyResultApprovalService::STATUS_PENDING;
            $electionResult->rejected_by = null;
            $electionResult->rejected_at = null;
            $electionResult->rejection_reason = null;
        }
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
    private function sanitizeFilenamePart($value){
        $value = trim((string) $value);
        if($value === ''){
            return 'unknown';
        }
        $value = preg_replace('/[^A-Za-z0-9]+/', '_', $value);
        $value = trim($value, '_');
        return $value !== '' ? $value : 'unknown';
    }
    public function decideResult($id, Request $request, ConstituencyResultApprovalService $approvalService){
        $validated = $request->validate([
            'action' => ['required', 'in:approve,reject,reopen'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $result = $approvalService->decide(
            (int) $id,
            Auth::user(),
            $validated['action'],
            $validated['reason'] ?? null,
            $request->ip(),
            $request->userAgent()
        );

        $message = [
            'approve' => 'Result approved and locked for national reporting.',
            'reject' => 'Result rejected and returned for correction.',
            'reopen' => 'Result reopened for correction. The action has been audited.',
        ][$validated['action']];
        $request->session()->flash('message', $message);

        return redirect()->back();
    }
    public function deleteResults($id,Request $request){
        $confirmResults = ElectionResult::where('id', $id)
            ->where('constituency_id', Auth::user()->constituency_id)
            ->first();
        if(!$confirmResults){
            abort(403);
        }
        if((int) $confirmResults->verify_by_constituency === 1 || $confirmResults->approval_status === ConstituencyResultApprovalService::STATUS_APPROVED){
            $request->session()->flash('error', 'Approved results cannot be deleted. Reopen the result and record a reason first.');
            return redirect()->back();
        }
        $delete = PartyElectionResult::where('election_result_id',$confirmResults->id)->delete();
        if($delete){
            $confirmResults->delete();
            $request->session()->flash('message', ' Success!');
        }else{

            $request->session()->flash('error', ' Error!');
        }
        return redirect()->back();
    }

    public function editResult($election_start_up, Request $request, $election_result_id=false, $user_id=false)
    {
        $result = null;
        if($election_result_id){
            $result = ElectionResult::where('id', $election_result_id)
                ->where('election_start_up_id', $election_start_up)
                ->where('constituency_id', Auth::user()->constituency_id)
                ->first();
            if(!$result){
                abort(403);
            }
            if((int) $result->verify_by_constituency === 1){
                $request->session()->flash('error', 'Confirmed results cannot be edited. Unconfirm first to continue.');
                return redirect(route('Director.Result'));
            }
        }
        if(!$user_id && $result){
            $user_id = $result->user_id;
        }

        $user = null;
        if($user_id){
            $user = User::select(
                'users.username',
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
                "pollingstation.total_voters",
                "users.constituency_id",
                "users.polling_station_id"
            )
            ->where('users.id', $user_id)
            ->leftJoin('user_type','user_type.id','=','users.user_type_id')
            ->leftJoin('region','region.id','=','users.region_id')
            ->leftJoin('constituency','constituency.id','=','users.constituency_id')
            ->leftJoin('electoralarea','electoralarea.id','=','users.electoralarea_id')
            ->leftJoin('pollingstation','pollingstation.id','=','users.polling_station_id')->first();
            if(!$user || (int) $user->constituency_id !== (int) Auth::user()->constituency_id){
                abort(403);
            }
        } elseif($result){
            $user = PollingStation::select(
                DB::raw("'N/A' as user_type_name"),
                'pollingstation.total_voters',
                'pollingstation.constituency_id',
                'pollingstation.id as polling_station_id',
                'region.name as region_name',
                'constituency.name as constituency_name',
                "pollingstation.name as PollingStation_name",
                "electoralarea.name as ElectoralArea_name",
                "pollingstation.polling_station_id as PollingStation_Id"
            )
            ->leftJoin('region','region.id','=','pollingstation.region_id')
            ->leftJoin('constituency','constituency.id','=','pollingstation.constituency_id')
            ->leftJoin('electoralarea','electoralarea.id','=','pollingstation.electoralarea_id')
            ->where('pollingstation.id', $result->polling_station_id)
            ->first();
            if(!$user || (int) $user->constituency_id !== (int) Auth::user()->constituency_id){
                abort(403);
            }
        } else {
            abort(403);
        }

        $electionStartupDetail = ElectionStartupDetail::select('election_type.name','election_startup_detail.*')
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("election_startup_detail.id",$election_start_up)
            ->where("status",1)
            ->first();
        if(!$electionStartupDetail){
            $request->session()->flash('error', ' Something went wrong!');

            return redirect(route("Director.election"));
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
            "election_result.pink_sheet_path",
            "election_result.election_start_up_id"

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)
        ->where('election_result.constituency_id',Auth::user()->constituency_id)
        ->where('election_result.election_start_up_id',$election_start_up)
        ->orderBy('candidates.ordering_position','ASC');


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }elseif($user_id){
            $electionResult = $electionResult->where('election_result.user_id',$user_id);
        }
        $electionResult =$electionResult->get();

        //dd($electionResult->toArray());

        return view('director.polling.editResult',compact('user_id','election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
    public function captureResult($election_start_up,$user_id,Request $request){
        $user = null;
        $inputElectionResultId = (int) $request->input('election_result_id', 0);

        $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
        )
        ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
        ->where("election_startup_detail.id",$election_start_up)

        ->where("status",1)->first();
        if(!$electionStartupDetail){
            $request->session()->flash('error', 'Something went wrong!');
            return redirect()->back();
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

        $e_r = null;
        if($inputElectionResultId > 0){
            $e_r = ElectionResult::where('id', $inputElectionResultId)
                ->where('election_start_up_id', $election_start_up)
                ->where('constituency_id', Auth::user()->constituency_id)
                ->first();
            if(!$e_r){
                $request->session()->flash('error', 'Result not found.');
                return redirect()->back();
            }
        }

        if(!$e_r){
            $user = User::select(
                'users.id as user_id',
                'user_type.id as user_type_id',
                'user_type.name as user_type_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "pollingstation.name as PollingStation_name",
                "electoralarea.name as ElectoralArea_name",
                "pollingstation.polling_station_id as PollingStation_Id",
                "users.country_id",
                "users.region_id",
                "users.constituency_id",
                "users.electoralarea_id",
                "users.polling_station_id"
            )
            ->where('users.id', $user_id)
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->join('region','region.id','=','users.region_id')
            ->join('constituency','constituency.id','=','users.constituency_id')
            ->join('electoralarea','electoralarea.id','=','users.electoralarea_id')
            ->join('pollingstation','pollingstation.id','=','users.polling_station_id')->first();
            if(!$user || (int) $user->constituency_id !== (int) Auth::user()->constituency_id){
                abort(403);
            }

            $e_r = ElectionResult::where('election_result.user_id',$user_id)
             ->where('election_result.election_start_up_id',$election_start_up)
             ->where('election_result.constituency_id', Auth::user()->constituency_id)
             ->where('election_result.polling_station_id', $user->polling_station_id)
             //->where('election_result.election_type_id',$electionStartupDetail->election_type_id)
                ->first();
        }

        if($e_r){
            if((int) $e_r->verify_by_constituency === 1){
                $request->session()->flash('error', 'Result is already confirmed.');
                return redirect()->back();
            }
            $e_r->approval_status = ConstituencyResultApprovalService::STATUS_PENDING;
            $e_r->rejected_by = null;
            $e_r->rejected_at = null;
            $e_r->rejection_reason = null;
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
                        ->where('polling_station_id',$e_r->polling_station_id)
                        ->where('candidate_id', $candidateId)
                        ->where('party_id', $partyId);
                    if(is_null($e_r->user_id)){
                        $partyElectionResult = $partyElectionResult->whereNull('user_id');
                    }else{
                        $partyElectionResult = $partyElectionResult->where('user_id', $e_r->user_id);
                    }
                    $partyElectionResult = $partyElectionResult->first();
                    if(!$partyElectionResult){
                        $partyElectionResult = new PartyElectionResult;
                        $partyElectionResult->user_id = $e_r->user_id;
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
            $obtainedVoteQuery = PartyElectionResult::where('election_result_id',$e_r->id)
                ->where('polling_station_id', $e_r->polling_station_id);
            if(is_null($e_r->user_id)){
                $obtainedVoteQuery = $obtainedVoteQuery->whereNull('user_id');
            }else{
                $obtainedVoteQuery = $obtainedVoteQuery->where('user_id', $e_r->user_id);
            }
            $e_r->obtained_votes = $obtainedVoteQuery->sum('obtained_vote');
            $e_r->save();
            $e_r->total_ballot = $e_r->obtained_votes +   $e_r->total_rejected_ballot;
            $e_r->save();
            $request->session()->flash('message', ' Election Result updated successfully!');


        }else{

            $electionResult = new ElectionResult;
            $electionResult->polling_station_id =  $user->polling_station_id;
            $electionResult->user_id = $user_id;
            $electionResult->user_type_id = $user->user_type_id;
            $electionResult->country_id = $user->country_id;
            $electionResult->region_id = $user->region_id;
            $electionResult->constituency_id = $user->constituency_id;
            $electionResult->electoral_area_id	 = $user->electoralarea_id;


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
                    $partyElectionResult->user_id = $user_id;
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
                ->where('user_id', $user_id)
                ->where('polling_station_id', $electionResult->polling_station_id)->sum('obtained_vote');

            $electionResult->save();
            $electionResult->total_ballot = $electionResult->obtained_votes +   $electionResult->total_rejected_ballot;
            $electionResult->save();
            $request->session()->flash('message', ' Election Result sent successfully!');
        }
        return redirect()->back();
    }
    public function resultsXlx($election_start_up, Request $request, $election_result_id=false){
        $user = User::select(
            'users.username',
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
            "pollingstation.total_voters",
            "election_result.verify_by_constituency",
            "election_result.verify_by_regional",

            "election_type.name as election_type_name",
            "election_startup_detail.election_name",
            'region.name as region_name',
            "constituency.name as constituency_name",
            "electoralarea.name as ElectoralArea_name",
            "pollingstation.name as PollingStation_name",
            "pollingstation.polling_station_id as PollingStation_code"


        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->join('pollingstation','pollingstation.id','=','election_result.polling_station_id')

        ->join('election_type','election_type.id','=','election_result.election_type_id')
        ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')

        ->join('region','region.id','=','election_result.region_id')
        ->join('constituency','constituency.id','=','election_result.constituency_id')
        ->join('electoralarea','electoralarea.id','=','election_result.electoral_area_id')
        //->join('pollingstation','pollingstation.id','=','election_result.polling_station_id')
        ->orderBy('candidates.ordering_position','ASC')
        ->where('election_result.id',$election_start_up);
        $styleArray = array(
            /* 'borders' => array(
                'outline' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => array('argb' => 'FFFF0000'),
                ),
            ), */
            'font' => array(
                'bold' => true
                )
        );
        $electionResults =$electionResult->get();
        //return view('director.polling.viewResults',compact('election_start_up','electionResult','parties','user','electionStartupDetail'));
        //dd($electionResults->toArray());
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheet=$spreadsheet->createSheet();
        $sheet->setTitle('Result');
        $sheet->setCellValue('A1',"Election");
        $sheet->getColumnDimension('A')->setWidth(20);

        $sheet->getStyle('A1')->applyFromArray($styleArray);
        $sheet->getStyle('B1')->applyFromArray($styleArray);
        $sheet->getStyle('C1')->applyFromArray($styleArray);
        $sheet->getStyle('D1')->applyFromArray($styleArray);
        $sheet->getStyle('E1')->applyFromArray($styleArray);
        $sheet->getStyle('F1')->applyFromArray($styleArray);
        $sheet->getStyle('G1')->applyFromArray($styleArray);
        $sheet->getStyle('I1')->applyFromArray($styleArray);
        $sheet->getStyle('J1')->applyFromArray($styleArray);

        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);

        $sheet->setCellValue('B1',"Election Type");
        $sheet->setCellValue('C1',"Region");
        $sheet->setCellValue('D1',"Constituency");
        $sheet->setCellValue('E1',"Electoral Area");
        $sheet->setCellValue('F1',"Polling Station");
        $sheet->setCellValue('G1',"Polling Station Code");
        $sheet->setCellValue('H1',"Candidate");
        $sheet->setCellValue('I1',"Political Party");
        $sheet->setCellValue('J1',"Votes Obtained");
        $count=2;
        foreach ($electionResults as $key => $electionResult) {
            $sheet->setCellValue('A'.$count,$electionResult->election_type_name);
            $sheet->setCellValue('B'.$count,$electionResult->election_name);
            $sheet->setCellValue('C'.$count,$electionResult->region_name);
            $sheet->setCellValue('D'.$count,$electionResult->constituency_name);
            $sheet->setCellValue('E'.$count,$electionResult->ElectoralArea_name);
            $sheet->setCellValue('F'.$count,$electionResult->PollingStation_name);
            $sheet->setCellValue('G'.$count,$electionResult->PollingStation_code);
            $sheet->setCellValue('H'.$count,$electionResult->first_name." ".$electionResult->last_name);
            $sheet->setCellValue('I'.$count,$electionResult->party_initial);
            $sheet->setCellValue('J'.$count,$electionResult->party_election_result_obtained_vote);
            $count++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Result'.$electionResults->toArray()[0]['election_name'].'".xlsx ');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;

    }
}

