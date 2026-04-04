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
use DataTables;
use DB;
use App\Model\Candidate;
use App\Model\PartyElectionResult;
use App\Model\ElectionResult;
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

    public function  country(){
        $countries = Country::select(
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.country_id = countries.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.country_id = countries.id) as total_polling"),
            DB::raw("(select count(id) from ElectoralArea where  ElectoralArea.country_id = countries.id) as total_electral"),
            DB::raw("(select count(id) from constituency where  constituency.country_id = countries.id) as total_constituency"),
            'countries.*'
        )->get();
        return view('admin.locations.country',compact('countries'));
    }
    public function  countryAdd(){
        $countries = Country::all();
        return view('admin.locations.NewCountry',compact('countries'));
    }
    public function  countryAddPost(Request $request){
        $data = $request->all();
        $countries  = Country::where('name',$data['name'])->first();
        if($countries){
            $request->session()->flash('error', ' Country already exist!');
            return redirect()->back();
        }
        $countries = Country::create($request->all());
        $request->session()->flash('message', ' Country added successfully!');

        return redirect(route('SuperAdmin.country'));
    }
    public function  countryEdit($id){
        $country = Country::where('id',$id)->first();
        return view('admin.locations.EditCountry',compact('country'));
    }
    public function  countryEditPost($id,Request $request){
        $data = $request->all();
        $countries  = Country::where('id',$id)->first();
        $countries ->name = $data['name'];
        $countries ->country_id = $data['country_id'];
        $countries ->save();
        $request->session()->flash('message', ' Country updated successfully!');
        return redirect()->back();
    }
    public function  countryDelete($id,Request $request){

        $found = Constituency::where('country_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Country  belong to this Constituency');
            return redirect()->back();
        }
        $found = ElectionResult::where('country_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Result belong to this Country');
            return redirect()->back();
        }
        $found = ElectoralArea::where('country_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Country  belong to this ElectralArea');
            return redirect()->back();
        }
        $found = PollingStation::where('country_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Country  belong to this polling station');
            return redirect()->back();
        }
        $found = Region::where('country_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Country  belong to this Region');
            return redirect()->back();
        }

        $found = User::where('country_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'User belong to this Country');
            return redirect()->back();
        }
        $countries  = Country::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', ' Country updated successfully!');
        return redirect()->back();
    }



    public function  region(){
        $countries = Country::all();
        return view('admin.locations.region',compact('countries'));
    }
    public function  regionAjax(Request $request){
        $regions = Region::select(
            'countries.id as c_id',
            'countries.name as country_name',
            'region.*',
            DB::raw("(select sum(total_voters) from PollingStation where  PollingStation.region_id = region.id) as total_voters"),
            DB::raw("(select count(id) from PollingStation where  PollingStation.region_id = region.id) as total_polling"),
            DB::raw("(select count(id) from ElectoralArea where  ElectoralArea.region_id = region.id) as total_electral"),
            DB::raw("(select count(id) from constituency where  constituency.region_id = region.id) as total_constituency")


            )
             ->join('countries','countries.id','=','region.country_id');
             if($request->input('country_id') != 'all')
             $regions = $regions ->where('region.country_id',$request->input('country_id'));
            return DataTables::of($regions)->make(true);
    }
    public function  regionAdd(){
        $countries = Country::all();
        return view('admin.locations.NewRegion',compact('countries'));
    }
    public function  regionAddPost(Request $request){
        $data = $request->all();
        $region  = Region::where('country_id',$data['country_id'])->where('name',$data['name'])->first();
        if($region){
            $request->session()->flash('error', ' Region already exist!');
            return redirect()->back();
        }
        $countries = Region::create($request->all());
        $request->session()->flash('message', ' Region added successfully!');

        return redirect(route('SuperAdmin.region'));
    }
    public function  regionEdit($id){
        $region = Region::select('countries.id as c_id','countries.name as country_name','region.*')
            ->join('countries','countries.id','=','region.country_id')
            ->where('region.id',$id)
            ->first();
            $countries = Country::all();
        return view('admin.locations.EditRegion',compact('region','countries'));
    }
    public function  regionEditPost($id,Request $request){
        $data = $request->all();
        $countries  = Region::where('id',$id)->first();
        $countries ->name = $data['name'];
        $countries ->country_id = $data['country_id'];
        $countries ->save();
        $request->session()->flash('message', ' Region updated successfully!');
        return redirect()->back();
    }
    public function  regionDelete($id,Request $request){
        $found = Candidate::where('region_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Candidate belong to this Region'.$found->name);
            return redirect()->back();
        }
        $found = Constituency::where('region_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Region  belong to this Constituency');
            return redirect()->back();
        }
        $found = ElectionResult::where('region_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Result belong to this Region');
            return redirect()->back();
        }
        $found = ElectoralArea::where('region_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'ElectoralArea  belong to this Region');
            return redirect()->back();
        }
        $found = PollingStation::where('region_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Region  belong to this polling station');
            return redirect()->back();
        }
        $found = User::where('region_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'User belong to this Region');
            return redirect()->back();
        }

        $countries  = Region::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', 'Region deleted!');
        return redirect()->back();
    }



    public function  constituency(){
        /* $regions = Constituency::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.*"
            )
            ->join('countries','countries.id','=','constituency.country_id')
            ->join('region','region.id','=','constituency.region_id')
            ->get(); */
            $regions = Region::orderBy('name','asc')->get();
        return view('admin.locations.constituency',compact('regions'));
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
        if($request->input('region_id') != 'all')
            $regions = $regions ->where('constituency.region_id',$request->input('region_id'));
        return DataTables::of($regions)->make(true);

    }
    public function  constituencyAdd(){
        $countries = Country::all();
        return view('admin.locations.NewConstituency',compact('countries'));
    }

    public function  getRegion(Request $request){
        $data = $request->all();
        $countries = Region::where('country_id',$data['country_id'])->get();
        return $countries;
    }
    public function  constituencyAddPost(Request $request){
        $data = $request->all();
        $region  = Constituency::where('region_id',$data['region_id'])->where('country_id',$data['country_id'])->where('name',$data['name'])->first();
        if($region){
            $request->session()->flash('error', ' Region already exist!');
            return redirect()->back();
        }
        $countries = Constituency::create($request->all());
        $request->session()->flash('message', ' Constituency added successfully!');

        return redirect(route('SuperAdmin.constituency'));
    }
    public function  constituencyEdit($id){
        $regions = Constituency::select(
            'countries.id as c_id',
            'countries.name as country_name',
            'region.name as region_name',
            "constituency.*"

        )
            ->join('countries','countries.id','=','constituency.country_id')
            ->join('region','region.id','=','constituency.region_id')

        ->where('constituency.id',$id)
        ->first();
            $countries = Country::all();
            $reg  = Region::where('country_id',$regions->country_id)->get();
        return view('admin.locations.EditConstituency',compact('regions','countries','reg'));
    }
    public function  constituencyEditPost($id,Request $request){
        $data = $request->all();
        $countries  = Region::where('id',$id)->first();
        $countries ->name = $data['name'];
        $countries ->country_id = $data['country_id'];
        $countries ->save();
        $request->session()->flash('message', ' Region updated successfully!');
        return redirect()->back();
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
        return view('admin.locations.ElectoralArea',compact('regions'));
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
    public function  ElectoralAreaAdd(){
        $countries = Country::all();
        return view('admin.locations.NewElectoralArea',compact('countries'));
    }
    public function  getConstituency(Request $request){
        $data = $request->all();
        $countries = Constituency::where('region_id',$data['region_id'])->get();
        return $countries;
    }
    public function  ElectoralAreaAddPost(Request $request){
        $data = $request->all();
        $region  = ElectoralArea::where('constituency_id',$data['constituency_id'])->where('region_id',$data['region_id'])->where('country_id',$data['country_id'])->where('name',$data['name'])->first();
        if($region){
            $request->session()->flash('error', '  ElectoralArea already exist!');
            return redirect()->back();
        }
        $countries = ElectoralArea::create($request->all());
        $request->session()->flash('message', '  ElectoralArea added successfully!');

        return redirect(route('SuperAdmin.ElectoralArea'));
    }

    public function  ElectoralAreaDelete($id,Request $request){
        $found = Candidate::where('electoral_area_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Candidate belong to this ElectoralArea'.$found->name);
            return redirect()->back();
        }
        $found = ElectionResult::where('electoral_area_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Result belong to this ElectoralArea');
            return redirect()->back();
        }
        $found = PollingStation::where('electoralarea_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'ElectoralArea Result belong to this polling station');
            return redirect()->back();
        }
        $found = User::where('electoralarea_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'User belong to this ElectraArea');
            return redirect()->back();
        }
        $countries  = ElectoralArea::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', ' ElectoralArea Deleted successfully!');
        return redirect()->back();
    }
    public function  ElectoralAreaEdit($id,Request $request){
        $electoralArea  = ElectoralArea::find($id);

        $countries = Country::all();
        $ps = PollingStation::where('region_id',$electoralArea->region_id)->get();
        $cc = Constituency::where('id',$electoralArea->constituency_id)->get();
        return view('admin.locations.EditElectoralArea',compact('cc','ps','electoralArea','countries'));
    }

    public function  constituencyDelete($id,Request $request){
        $found = Candidate::where('constituency_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Candidate belong to this Constituency'.$found->name);
            return redirect()->back();
        }
        $found = ElectionResult::where('constituency_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Result belong to this Constituency');
            return redirect()->back();
        }
        $found = ElectoralArea::where('constituency_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' ElectralArea to this Constituency');
            return redirect()->back();
        }
        $found = PollingStation::where('constituency_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Constituency belong to Polling Station');
            return redirect()->back();
        }

        $found = User::where('constituency_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Manager belong to this Constituency'.$found->name);
            return redirect()->back();
        }
        $countries  = Constituency::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', ' Constituency Deleted successfully!');
        return redirect()->back();
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
        return view('admin.locations.PollingStatus',compact('regions'));
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
    public function  PollingStationAdd(){
        $countries = Country::all();
        return view('admin.locations.NewPollingStation',compact('countries'));
    }
    public function PollingStationDelete($id,Request $request){
        $found = Candidate::where('polling_station_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Candidate belong to this polling station'.$found->name);
            return redirect()->back();
        }
        $found = ElectionResult::where('polling_station_id',$id)->first();
        if($found){
            $request->session()->flash('error', ' Result belong to this polling station');
            return redirect()->back();
        }
        $found = PartyElectionResult::where('polling_station_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Party Election Result belong to this polling station');
            return redirect()->back();
        }
        $found = User::where('polling_station_id',$id)->first();
        if($found){
            $request->session()->flash('error', 'Manager belong to this polling station'.$found->name);
            return redirect()->back();
        }

        $PollingStation =  PollingStation::find($id);
        $PollingStation->delete();
        $request->session()->flash('message', '  Polling Station deleted successfully!');
        return redirect()->back();
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

    public function  PollingStationAddPost(Request $request){

        $validation =  Validator::make($request->all(), [
            'polling_station_id' => ['required', 'string', 'min:6', 'max:14', 'unique:PollingStation'],
        ]);

        if ($validation->fails()) {
            return redirect()->back()
                        ->withErrors($validation)
                        ->withInput();

        }else{
            $data = $request->all();
            $region  = PollingStation::where('electoralarea_id',$data['electoralarea_id'])->where('constituency_id',$data['constituency_id'])->where('region_id',$data['region_id'])->where('country_id',$data['country_id'])->where('name',$data['name'])->first();
            if($region){
                $request->session()->flash('error', 'Polling Station already exist!');
                return redirect()->back();
            }
            $countries = PollingStation::create($request->all());
            $request->session()->flash('message', '  Polling Station added successfully!');

            return redirect(route('SuperAdmin.PollingStation'));
        }

    }
    public function PollingStationEdit($id){
        $polling = PollingStation::select(
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
        ->where('PollingStation.id',$id)
        ->first();
        $countries = Country::all();
        //$ps = PollingStation::where('region_id',$polling->region_id)->get();
        $region = Region::where('id',$polling->region_id)->get();
        $cc = Constituency::where('id',$polling->constituency_id)->get();
        $ee = ElectoralArea::where('id',$polling->electoralarea_id)->get();
        return view('admin.locations.EditPollingStation',compact('ee','cc','region','polling','countries','reg'));
    }
    public function PollingStationEditPost($id, Request $request){
        $polling = PollingStation::find($id);
        $polling->name = $request->input('name');
        $polling->country_id = $request->input('country_id');
        $polling->region_id = $request->input('region_id');
        $polling->constituency_id = $request->input('constituency_id');
        $polling->polling_station_id = $request->input('polling_station_id');

        $polling->electoralarea_id = $request->input('electoralarea_id');
        $polling->total_voters = $request->input('total_voters');
        $polling->save();

        $request->session()->flash('message', '  Polling Station updated successfully!');

            return redirect(route('SuperAdmin.PollingStation'));
    }
    public function ElectoralAreaEditPost($id, Request $request){
        $polling = ElectoralArea::find($id);
        $polling->name = $request->input('name');
        $polling->country_id = $request->input('country_id');
        $polling->region_id = $request->input('region_id');
        $polling->constituency_id = $request->input('constituency_id');

        $polling->save();

       $request->session()->flash('message', '  ElectoralArea updated successfully!');

        return redirect(route('SuperAdmin.ElectoralArea'));
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
