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
        $countries = Country::all();
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
        $countries  = Country::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', ' Country updated successfully!');
        return redirect()->back();
    }



    public function  region(){
        $regions = Region::select('countries.id as c_id','countries.name as country_name','region.*')
            ->join('countries','countries.id','=','region.country_id')->get();
        return view('admin.locations.region',compact('regions'));
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
        $countries  = Country::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', 'Country updated successfully!');
        return redirect()->back();
    }



    public function  constituency(){
        $regions = Constituency::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.*"
            )
            ->join('countries','countries.id','=','constituency.country_id')
            ->join('region','region.id','=','constituency.region_id')
            ->get();
        return view('admin.locations.constituency',compact('regions'));
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
        $regions = ElectoralArea::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "ElectoralArea.*"
            )
            ->join('countries','countries.id','=','ElectoralArea.country_id')
            ->join('region','region.id','=','ElectoralArea.region_id')
            ->join('constituency','constituency.id','=','ElectoralArea.constituency_id')
            ->get();
        return view('admin.locations.ElectoralArea',compact('regions'));
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
        $countries  = ElectoralArea::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', ' ElectoralArea Deleted successfully!');
        return redirect()->back();
    }
    public function  constituencyDelete($id,Request $request){
        $countries  = Constituency::where('id',$id)->first();
        $countries ->delete();
        $request->session()->flash('message', ' Constituency Deleted successfully!');
        return redirect()->back();
    }
    public function  PollingStation(){
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
            ->join('ElectoralArea','ElectoralArea.id','=','PollingStation.electoralarea_id')
            ->get();
        return view('admin.locations.PollingStatus',compact('regions'));
    }
    public function  PollingStationAdd(){
        $countries = Country::all();
        return view('admin.locations.NewPollingStation',compact('countries'));
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

}
