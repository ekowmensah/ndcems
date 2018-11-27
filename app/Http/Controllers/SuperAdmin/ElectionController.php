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
        return view('admin.election.newElectionType');
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

}
