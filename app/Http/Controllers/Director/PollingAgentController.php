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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


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
            ->where('constituency.id', Auth::user()->constituency_id)
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
                    $user->secret = $request->input('password');
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
        $user = User::find($id);
        $user->delete();
        $request->session()->flash('message', ' Agent Deleted Successfully!');
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
                $data['secret'] = $request->input('password');
            }


            User::create($data);
            $request->session()->flash('message', ' User Created Successfully!');
            return redirect(route('SuperAdmin.Users'));
        }


    }
    public function result(){
        $election = ElectionType::all();

            return view('director.polling.result',compact('election'));
    }
    public function pollingStationResultAajax(Request $request ){
        $regions = PollingStation::select(
            'PollingStation.*',
            'PollingStation.total_voters',
            'election_result.obtained_votes',
            'election_result.total_ballot',
            'election_result.user_id as election_result_user_id',
            'election_result.total_rejected_ballot',
            'election_result.id as election_result_id',
            'election_result.election_start_up_id',
            "election_result.verify_by_constituency",
            "election_result.verify_by_regional",
            "election_result.result_by_constituency",
            'election_startup_detail.election_name',
            'election_type.name as election_type_name',
            'users.name as agent_name',
            'users.phoneno as agent_phoneno'
        )
        ->join('election_result','election_result.polling_station_id','=','PollingStation.id')
        ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')
        ->join('election_type','election_result.election_type_id','=','election_type.id')
        //->join("users","users.constituency_id","=","election_result.constituency_id")
        //->where('election_result.constituency_id',Auth::user()->constituency_id)
        ->leftJoin('users','users.id','=','election_result.result_by_constituency')

        ;
        //->distinct('PollingStation.id')

        //->leftJoin('users','users.id','=','election_result.result_by_constituency')

        /* ->join("users",function($join){
            $join//->on("users.constituency_id","=","election_result.constituency_id");
                ->on("users.id","=","election_result.result_by_constituency")
                ->orWhere('election_result.result_by_constituency', '=', Auth::user()->id);
        })
        ->get(); */

        //->leftJoin('PollingStation','PollingStation.constituency_id','=','constituency.id');
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
        /* if(!$electionStartupDetail){
            $request->session()->flash('error', ' Something went wrong!');

            return redirect(route("Agent.election"));
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
            "PollingStation.total_voters",
            "election_result.verify_by_constituency",
            "election_result.verify_by_regional"
        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('election_result.user_id',Auth::user()->id)
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        //->where('candidates.election_id',$electionStartupDetail->election_type_id)
        ->orderBy('candidates.ordering_position','ASC')
        ->where('election_result.id',$election_start_up);
        //->orderBy('candidates.ordering_position','ASC');
            //dd($electionResult);

        /* if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        } */
        $electionResult =$electionResult->get();

        //dd($electionResult);

        return view('director.polling.viewResults',compact('election_start_up','electionResult','parties','user','electionStartupDetail'));
        //return view('director.polling.viewResults');

    }
    public function confirmResults($id){
        $confirmResults = ElectionResult::find($id);
        if($confirmResults->verify_by_constituency == 0){
             $confirmResults->verify_by_constituency = 1;
        }else if($confirmResults->verify_by_constituency == 1){
            $confirmResults->verify_by_constituency = 0;
        }
        $confirmResults->save();
        return redirect()->back();
    }
    public function deleteResults($id,Request $request){
        $confirmResults = ElectionResult::find($id);
        $delete = PartyElectionResult::where('election_result_id',$confirmResults->id)->delete();
        if($delete){
            $confirmResults->delete();
            $request->session()->flash('message', ' Success!');
        }else{

            $request->session()->flash('error', ' Error!');
        }
        return redirect()->back();
    }

    public function editResult($election_start_up,$election_result_id=false ,$user_id ,Request $request)
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
            "election_result.election_start_up_id"

        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        //->where('candidates.polling_station_id',Auth::user()->polling_station_id)
        ->where('candidates.election_id',$electionStartupDetail->election_type_id)
        ->where('election_result.user_id',$user_id)
        ->where('election_result.election_start_up_id',$election_start_up)
        ->orderBy('candidates.ordering_position','ASC');


        if( $election_result_id){

            $electionResult = $electionResult->where('election_result.id',$election_result_id);
        }
        $electionResult =$electionResult->get();

        //dd($electionResult->toArray());

        return view('director.polling.editResult',compact('user_id','election_start_up','electionResult','parties','user','electionStartupDetail'));
    }
    public function captureResult($election_start_up,$user_id,Request $request){
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
        ->where('users.id', $user_id)
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
        $posted_Data = $request->all();


        $e_r = ElectionResult::where('election_result.user_id',$user_id)
         ->where('election_result.election_start_up_id',$election_start_up)
         //->where('election_result.election_type_id',$electionStartupDetail->election_type_id)
            ->first();

        if($e_r){
            $e_r->total_ballot =0;
            $e_r->total_rejected_ballot = $request->input('total_rejected_ballot');
            $e_r->save();

            foreach ($posted_Data['party'] as $key => $value) {
                $party_id = key($value);
                $partyElectionResult = PartyElectionResult::where('user_id',$user_id)
                    ->where('election_result_id',$e_r->id)
                    ->where('polling_station_id',$e_r->polling_station_id)
                    ->where('party_id',$party_id);


                    foreach($value as $key => $_value){
                        $candidate_id = key($_value);
                        $partyElectionResult = $partyElectionResult->where('candidate_id', key($_value))->first();
                        foreach($_value as $key => $__value){
                            $obtained_vote =  $__value;
                            $partyElectionResult->obtained_vote = $__value;
                            $partyElectionResult->save();
                        }

                    }
                $partyElectionResult->save();
            }
            $e_r->obtained_votes = PartyElectionResult::where('election_result_id',$e_r->id)
                ->where('user_id', $user_id)
                ->where('polling_station_id', $e_r->polling_station_id)->sum('obtained_vote');
            $e_r->save();
            $e_r->total_ballot = $e_r->obtained_votes +   $e_r->total_rejected_ballot;
            $e_r->save();
            $request->session()->flash('message', ' Election Result updated successfully!');


        }else{

            $electionResult = new ElectionResult;
            $electionResult->polling_station_id =  Auth::user()->polling_station_id;
            $electionResult->user_id = $user_id;
            $electionResult->user_type_id = Auth::user()->user_type_id;
            $electionResult->country_id = Auth::user()->country_id;
            $electionResult->region_id = Auth::user()->region_id;
            $electionResult->constituency_id = Auth::user()->constituency_id;
            $electionResult->electoral_area_id	 = Auth::user()->electoralarea_id;


            $electionResult->election_type_id = $electionStartupDetail->election_type_id;
            $electionResult->election_start_up_id = $electionStartupDetail->id;
            $electionResult->obtained_votes = 0;

            $electionResult->total_ballot = 0;
            $electionResult->total_rejected_ballot =$request->input('total_rejected_ballot');
            $electionResult->save();
        foreach ($posted_Data['party'] as $key => $value) {
                $partyElectionResult = new PartyElectionResult;
                $partyElectionResult->user_id = $user_id;
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
                ->where('user_id', $user_id)
                ->where('polling_station_id', $electionResult->polling_station_id)->sum('obtained_vote');

            $electionResult->save();
            $electionResult->total_ballot = $electionResult->obtained_votes +   $electionResult->total_rejected_ballot;
            $electionResult->save();
            $request->session()->flash('message', ' Election Result sent successfully!');
        }
        return redirect()->back();
    }
    public function resultsXlx($election_start_up,$election_result_id=false , Request $request){
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
            "PollingStation.total_voters",
            "election_result.verify_by_constituency",
            "election_result.verify_by_regional",

            "election_type.name as election_type_name",
            "election_startup_detail.election_name",
            'region.name as region_name',
            "constituency.name as constituency_name",
            "ElectoralArea.name as ElectoralArea_name",
            "PollingStation.name as PollingStation_name",
            "PollingStation.polling_station_id as PollingStation_code"


        )
        ->join('party_election_result','party_election_result.election_result_id','=','election_result.id')
        ->join('political_party','political_party.id','=','party_election_result.party_id')
        ->join('candidates','candidates.id','=','party_election_result.candidate_id')
        ->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')

        ->join('election_type','election_type.id','=','election_result.election_type_id')
        ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')

        ->join('region','region.id','=','election_result.region_id')
        ->join('constituency','constituency.id','=','election_result.constituency_id')
        ->join('ElectoralArea','ElectoralArea.id','=','election_result.electoral_area_id')
        //->join('PollingStation','PollingStation.id','=','election_result.polling_station_id')
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
