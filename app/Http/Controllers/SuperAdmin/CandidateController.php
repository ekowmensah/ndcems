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
use App\Model\Candidate;
use DataTables;
use App\Model\ElectionStartupDetail;


class CandidateController extends Controller
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

    public function candidate($id=false){
        $_electionTypes = ElectionType::all();
        $Constituencies = Constituency::all();
        $regions = Region::all();
        $type = ElectionType::where('id',$id)->first();

        return view('admin.candidate.candidate',compact('type','id','regions','Constituencies','_electionTypes'));
    }
    public function candidateRegister($id){
        $electionTypes = ElectionType::all();
        $_PoliticalParties = PoliticalParty::select('political_party.name','political_party.id')->get();
        $type = ElectionType::find($id);
        $electionTypes = ElectionType::where('id','<=',$id)->get();
        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
        }

        if(!isset($NewElectionTypes[1])){
            $PoliticalParties = PoliticalParty::select('candidates.election_id','candidates.party_id','candidates.first_name','political_party.id','political_party.name')
            ->leftJoin('candidates', function($join) use ($NewElectionTypes)
            {
                $join->on('candidates.party_id' , '=','political_party.id')
                ->where('candidates.election_id','=',$NewElectionTypes[0]['id']);
            });
            $PoliticalParties = $PoliticalParties->whereNull('candidates.party_id');
            $PoliticalParties = $PoliticalParties->get();

        }else{
            $PoliticalParties = PoliticalParty::select('political_party.name','political_party.id')->get();
        }

        $country = Country::all();
        $country = $country->toArray();
        $electionStartupDetail = ElectionStartupDetail::where('election_type_id',$id)->get();
        return view('admin.candidate.candidate_register',compact('electionStartupDetail','type','NewElectionTypes','country','electionTypes','PoliticalParties'));
    }

    public function candidateRegisterPost(Request $request){




        $PoliticalParty = Candidate::create($request->all());
        if($request->file('logo')){
            $file = $request->file('logo');
            //$destinationPath  = storage_path('candidate_logo');
            //$file->move($destinationPath,$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $file->move(public_path('/candidate_logo'),$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $PoliticalParty->photo = $PoliticalParty->id.'.'.$file->getClientOriginalExtension();
            $PoliticalParty->save();
        }
        $request->session()->flash('message', 'Candidate registered successfully!');
       // return redirect(route("SuperAdmin.candidate"));
       return redirect()->back();

    }
    public function VerifyPositioningOrdering(Request $request){
        $data = $request->all();
        $username =  Candidate::where('ordering_position',$data['ordering_position'])
        ->where('election_start_up_id',$data['election_start_up_id']);
        //->first();
        if(isset($data['constituency_id']))
            $username = $username->where('constituency_id',$data['constituency_id']);
        $username = $username->first();
        if($username)
            return "faund";
        else
            return "not_found";
    }
    public function candidateAjax(Request $request){
        $candidate = Candidate::select(
            'election_type.name as election_type_name',
            'political_party.name as political_party_name',
            'region.name as region_name',
            'constituency.name as constituency_name',
            'PollingStation.name as polling_station_name',
            'election_startup_detail.election_name',
            'candidates.*'
            )
            ->join('election_type','election_type.id','=','candidates.election_id')
            ->join('election_startup_detail','election_startup_detail.id','=','candidates.election_start_up_id')
            ->join('political_party','political_party.id','=','candidates.party_id')
            ->leftJoin('region','region.id','=','candidates.region_id')
            ->leftJoin('constituency','constituency.id','=','candidates.constituency_id')
            ->leftJoin('PollingStation','PollingStation.id','=','candidates.polling_station_id')
        ->where("candidates.is_disabled",0)
        ;
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
    public function candidateEdit($id){

       $candidate = Candidate::find($id);

        $electionTypes = ElectionType::all();
        $PoliticalParties = PoliticalParty::all();
        $type = ElectionType::find($candidate->election_id);
        $electionTypes = ElectionType::where('id','<=',$candidate->election_id)->get();
        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                    "index"=>$i
            ];
            $NewElectionTypes[] = array_merge($value,array_merge($value,$data));
        }

        $country = Country::all();
        $country = $country->toArray();

        $regions = Region::all();
        $Constituencies = Constituency::where('region_id',$candidate->region_id)->get();
        $ElectoralAreas = ElectoralArea::where('constituency_id',$candidate->constituency_id)->get();
        $pollings = PollingStation::where('electoralarea_id',$candidate->electoral_area_id)->get();

        $electionStartupDetail = ElectionStartupDetail::all();

        return view('admin.candidate.candidate_edit',compact('electionStartupDetail','pollings','ElectoralAreas','Constituencies','regions','candidate','type','NewElectionTypes','country','electionTypes','PoliticalParties'));

    }

    public function candidateEditPost($id,Request $request){
        //$PoliticalParty = Candidate::where('id',$id)->update($request->all());
        $PoliticalParty = Candidate::find($id);
        $PoliticalParty->first_name = $request->input('first_name');
        $PoliticalParty->last_name= $request->input('last_name');
        $PoliticalParty->dob	= $request->input('dob');
        $PoliticalParty->election_id= $request->input('election_id');
        $PoliticalParty->personal= $request->input('personal');
        $PoliticalParty->party_id= $request->input('party_id');
        $PoliticalParty->region_id= $request->input('region_id');
        $PoliticalParty->constituency_id= $request->input('constituency_id');
        $PoliticalParty->ordering_position= $request->input('ordering_position');
        $PoliticalParty->polling_station_id= $request->input('polling_station_id');
        $PoliticalParty->id_no= $request->input('id_no');
        $PoliticalParty->phone= $request->input('phone');
        $PoliticalParty->electoral_area_id= $request->input('electoral_area_id');
        $PoliticalParty->election_start_up_id= $request->input('election_start_up_id');

        $PoliticalParty->save();
        if($request->file('photo')){
            $file = $request->file('photo');
            if(file_exists(public_path('/candidate_logo').$PoliticalParty->photo) && $PoliticalParty->photo)
                unlink(public_path('/candidate_logo').$PoliticalParty->photo);
            //$destinationPath  = storage_path('candidate_logo');
            //$file->move($destinationPath,$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $file->move(public_path('/candidate_logo'),$PoliticalParty->id.'.'.$file->getClientOriginalExtension());
            $PoliticalParty->photo = $PoliticalParty->id.'.'.$file->getClientOriginalExtension();
            $PoliticalParty->save();
        }
        $request->session()->flash('message', 'Candidate updated successfully!');
        return redirect(route("SuperAdmin.candidate"));
    }
    public function candidateDelete($id,Request $request){
        $PoliticalParty = Candidate::find($id);
        if(file_exists(public_path('/candidate_logo').$PoliticalParty->photo))
            unlink(public_path('/candidate_logo').$PoliticalParty->photo);
            $PoliticalParty ->delete();
            $request->session()->flash('message', 'Candidate deleted!');
            return redirect()->back();
    }
}
