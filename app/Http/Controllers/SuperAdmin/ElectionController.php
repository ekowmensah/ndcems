<?php

namespace App\Http\Controllers\SuperAdmin;

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
use App\Model\ElectionType;
use App\Model\PoliticalParty;
use DataTables;
use DB;
use Carbon\Carbon;
use App\Model\ElectionStartupDetail;
class ElectionController extends Controller
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

    public function electionType(){
        $electionTypes = ElectionType::all();
        return view('admin.election.electionType',compact('electionTypes'));
    }
    public function newElectionType(Request $request){
        return view('admin.election.NewElectionType');
    }
    public function newElectionTypePost(Request $request){

        $data = $request->all();
        $UserType  = ElectionType::where('name',$data['name'])->first();
        if($UserType){
            $request->session()->flash('error', ' Type already exist!');
            return redirect()->back();
        }
        ElectionType::create($data);
        $request->session()->flash('message', 'Type created successfully!');
        return redirect(route("SuperAdmin.electionType"));
    }
    public function electionTypesEdit($id){
        $electionType =  ElectionType::find($id);
        return view('admin.election.EditElectionType',compact('electionType'));
    }
    public function electionTypesEditPost($id,Request $request){

        $data = $request->all();
        $UserType  = ElectionType::where('id',$id)->first();
        $UserType->name = $data['name'];
        $UserType->save();
        $request->session()->flash('message', 'Type updated successfully!');
        return redirect(route("SuperAdmin.electionType"));
    }
    public function electionTypesDelete($id,Request $request){

        try{
            $UserTypes = ElectionType::findOrFail($id);
            $UserTypes->delete();
        }catch(\Exception $e){
            $request->session()->flash('error', 'Type already in use.!');
            return redirect()->back();

        }

        $request->session()->flash('message', 'Type deleted successfully!');
        return redirect(route("SuperAdmin.electionType"));
    }

    public function politicalParty(){

        return view('admin.political_party.political_party');
    }
    public function politicalPartyAjax(){
        $electionTypes = PoliticalParty::all();

        return DataTables::of($electionTypes)->make(true);
    }
    public function newPoliticalParty(Request $request){
        return view('admin.political_party.NewPoliticalParty');
    }
    public function newPoliticalPartyPost(Request $request){
        $PoliticalParty = PoliticalParty::create($request->all());
        if($request->file('logo')){
            $file = $request->file('logo');
            $destinationPath  = storage_path('party_logo');
            //$file->move($destinationPath,$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $file->move(public_path('/party_logo'),$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $PoliticalParty->logo = $PoliticalParty->id.'.'.$file->getClientOriginalExtension();
            $PoliticalParty->save();
        }

        $request->session()->flash('message', 'Party added successfully!');
        return redirect(route("SuperAdmin.politicalParty"));
    }
    public function editPoliticalParty($id){
        $PoliticalParty = PoliticalParty::find($id);
        return view('admin.political_party.editPoliticalParty',compact('PoliticalParty'));
    }
    public function editPoliticalPartyPost($id,Request $request){

        $data = $request->all();
        $PoliticalParty = PoliticalParty::find($id);
        $PoliticalParty->name =$data['name'];
        $PoliticalParty->party_id =  $data['party_id'];
        $PoliticalParty->party_initial  = $data['party_initial'];
        if($request->file('logo')){
            $file = $request->file('logo');
            $destinationPath  = storage_path('party_logo');
            //$file->move($destinationPath,$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $file->move(public_path('/party_logo'),$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $PoliticalParty->logo = $PoliticalParty->id.'.'.$file->getClientOriginalExtension();

        }
        $PoliticalParty->save();
        $request->session()->flash('message', 'Party updated successfully!');
        return redirect(route("SuperAdmin.politicalParty"));
    }
    public function DeletePoliticalParty($id,Request $request){
        $PoliticalParty = PoliticalParty::find($id);
        $PoliticalParty ->delete();
        $request->session()->flash('message', 'Party deleted successfully!');
        return redirect(route("SuperAdmin.politicalParty"));
    }
    public function election(){
        $electionTypes =  ElectionType::select(
            'election_startup_detail.id as election_startup_detail_id',
            'election_startup_detail.status',
            'election_startup_detail.election_name',
            'election_type.*'
        )
        ->join('election_startup_detail','election_type.id','=','election_startup_detail.election_type_id')
        ->get();
        //dd($electionTypes->toArray());
        return view('admin.election.election',compact('electionTypes'));
    }
    public function electionNew($id=false){
        $electionTypes =  ElectionType::all();
        $countries = Country::select(
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.country_id = countries.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.country_id = countries.id) as total_polling"),
            DB::raw("(select count(id) from ElectoralArea where  ElectoralArea.country_id = countries.id) as total_electral"),
            DB::raw("(select count(id) from constituency where  constituency.country_id = countries.id) as total_constituency"),
            'countries.*'
        )->get();
        $election = ElectionStartupDetail::where('election_type_id',$id)->first();

        return view('admin.election.electionNew',compact('election','electionTypes','countries'));
    }
    public function electionDetail($id){
        $countries = Country::select(
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.country_id = countries.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.country_id = countries.id) as total_polling"),
            DB::raw("(select count(id) from ElectoralArea where  ElectoralArea.country_id = countries.id) as total_electral"),
            DB::raw("(select count(id) from constituency where  constituency.country_id = countries.id) as total_constituency"),
            'countries.*'
        )->get();
        $election = ElectionStartupDetail::where('id',$id)->first();
        $electionType =  ElectionType::find($election->election_type_id);


        return view('admin.election.electionDetail',compact('election','electionType','countries'));
    }
    public function electionNewPost($id=false,Request $request){
        $data = $request->all();
        $date = $request->input('date');
        list($start, $end) = explode('-', strval($date));
        $data['start'] = Carbon::parse($start)->format('Y-m-d H:i:s');
        $data['end'] = Carbon::parse($end)->format('Y-m-d 23:59:59');
        $election = ElectionStartupDetail::where('election_type_id',$id)->first();
        if($election){
            $election->election_name = $data['election_name'];
            $election->start =$data['start'];
            $election->end=$data['end'];
            $election->total_constituency=$data['total_constituency'];
            $election->total_electral=$data['total_electral'];
            $election->total_polling=$data['total_polling'];
            $election->total_voters=$data['total_voters'];
            $election->status=1;
            $election->save();

        }else {
            # code...
            $ElectionStartupDetail = ElectionStartupDetail::create($data);
            $ElectionStartupDetail->status = 1;
            $ElectionStartupDetail->save();

        }
        $request->session()->flash('message', 'Election Started!');
            return redirect(route("SuperAdmin.election"));

    }
    public function electionDetailPost($id,Request $request){
        $data = $request->all();
        $date = $request->input('date');
        list($start, $end) = explode('-', strval($date));
        $data['start'] = Carbon::parse($start)->format('Y-m-d H:i:s');
        $data['end'] = Carbon::parse($end)->format('Y-m-d 23:59:59');
        $election = ElectionStartupDetail::where('id',$id)->first();
        if($election){
            $election->election_name = $data['election_name'];
            $election->start =$data['start'];
            $election->end=$data['end'];
            $election->total_constituency=$data['total_constituency'];
            $election->total_electral=$data['total_electral'];
            $election->total_polling=$data['total_polling'];
            $election->total_voters=$data['total_voters'];
            $election->status=1;
            $election->save();

        }else {
            # code...
            $ElectionStartupDetail = ElectionStartupDetail::create($data);
            $ElectionStartupDetail->status = 1;
            $ElectionStartupDetail->save();

        }
        $request->session()->flash('message', 'Election Started!');
            return redirect(route("SuperAdmin.election"));

    }
    public function electionDetailTougle($id,$tougle){
        $election = ElectionStartupDetail::find($id);
        $election->status=$tougle;
            $election->save();
            return redirect()->back();
    }

}
