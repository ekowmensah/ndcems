<?php

namespace App\Http\Controllers\National;

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
use App\Model\ElectionResult;
use App\Model\ElectionType;
use App\Model\Candidate;
use App\Model\PoliticalParty;
use App\Services\National\NationalDashboardService;
use App\Services\National\NationalResultAnalyticsService;


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

    public function  dashboard(){
        return view('national.home.index', app(NationalDashboardService::class)->getDashboardData());
    }
    public function presidentialResultAjax(){
        return app(NationalDashboardService::class)->getPresidentialChartData();
    }
    public function Presidential(){
        return view('region.home.presidential');

    }
    public function constituencyAajax(Request $request ){
        $regions = Constituency::select(
            'countries.id as c_id',
            'countries.name as country_name',

            "constituency.id",
            "constituency.name",
            'region.name as region_name',
            DB::raw("(select sum(total_voters) from pollingstation where  pollingstation.constituency_id = constituency.id) as total_voters"),
            DB::raw("(select count(id) from pollingstation where  pollingstation.constituency_id = constituency.id) as total_polling"),
            DB::raw("(select count(id) from electoralarea where  electoralarea.constituency_id = constituency.id) as total_electral")

        )
        /* ->join('constituency', function($join)
        {
           // $join->on('users.id', '=', 'contacts.user_id');
           $join->on('countries','countries.id','=','constituency.country_id');
           $join->on('region','region.id','=','constituency.region_id');
           $join->on('pollingstation','pollingstation.constituency_id','=','constituency.id');
        }); */
        ->join('countries','countries.id','=','constituency.country_id')
        ->join('region','region.id','=','constituency.region_id');
        //->leftJoin('pollingstation','pollingstation.constituency_id','=','constituency.id');
        //if($request->input('region_id') != 'all')
            $regions = $regions ->where('constituency.region_id',Auth::user()->region_id);
        return DataTables::of($regions)->make(true);

    }
    public function constituencyView(Request $request, $id)
    {
        return view(
            'national.home.constituencyResultView',
            $this->resultAnalytics()->getConstituencyDetail((int) $id, $this->requestStartupId($request))
        );
    }

    public function regionalResultView(Request $request, $id, $regional_id)
    {
        return view(
            'national.home.regionalResultView',
            $this->resultAnalytics()->getRegionDetail((int) $regional_id, $this->requestStartupId($request))
        );
    }
    public function PresidentialResult(Request $request){
        return view(
            "national.home.regionalPresidential",
            $this->resultAnalytics()->getPresidentialPageData($this->requestStartupId($request))
        );
    }
    public function PresidentialAajax(Request $request){
        return DataTables::of(
            $this->resultAnalytics()->getRegionalRows(null, $this->requestStartupId($request))
        )->make(true);
    }
    public function ConstituencyResult(Request $request){
        return view(
            "national.home.constituencyResult",
            $this->resultAnalytics()->getParliamentaryPageData($this->requestStartupId($request))
        );
    }
    public function ConstituencyResultAajax(Request $request){
        return DataTables::of(
            $this->resultAnalytics()->getConstituencyRows(null, $this->requestStartupId($request))
        )->make(true);
    }

    public function pollingAgent(){
        $UserType = UserType::latest()->first();
        /* $Users = User::select('users.created_at','users.name as user_name','users.id as user_id','user_type.id as user_type_id','user_type.name as user_type_name')
            ->join('user_type','user_type.id','=','users.user_type_id')
            ->where('user_type.id', $UserType->id)
            ->get(); */
        //$UserTypes = UserType::orderBy('id','desc')->get();
        //return view('admin.user.Users',compact('Users','UserTypes'));
        $electionTypes = Region::all();
        //return view("admin.polling_agent.polling_agent",compact('electionTypes','UserType'));

        return view("national.agent.agent",compact('electionTypes','UserType'));
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
                "pollingstation.name as PollingStation_name",
                "electoralarea.name as ElectoralArea_name"
            )
            ->where('user_type.id', $UserType->id)
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
    public function candidate($id=false){
        $_electionTypes = ElectionType::all();
        $Constituencies = Constituency::all();
        $regions = Region::all();
        $type = ElectionType::where('id',$id)->first();

        return view('national.agent.candidate',compact('type','id','regions','Constituencies','_electionTypes'));
    }
    public function candidateAjax(Request $request){
        $candidate = Candidate::select(
            'election_type.name as election_type_name',
            'political_party.name as political_party_name',
            'region.name as region_name',
            'constituency.name as constituency_name',
            'pollingstation.name as polling_station_name',
            'candidates.*'
            )
            ->join('election_type','election_type.id','=','candidates.election_id')
            ->join('political_party','political_party.id','=','candidates.party_id')
            ->leftJoin('region','region.id','=','candidates.region_id')
            ->leftJoin('constituency','constituency.id','=','candidates.constituency_id')
            ->leftJoin('pollingstation','pollingstation.id','=','candidates.polling_station_id')
        ->where("candidates.is_disabled",0);
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
            $candidate = $candidate->where('candidates.electoral_area_id',$request->input('electoralarea_id'));
        if($request->input('constituency_id') != "all")
            $candidate = $candidate->where('constituency.id',$request->input('constituency_id'));
        if($request->input('region_id') != "all")
            $candidate = $candidate->where('region.id',$request->input('region_id'));
        return DataTables::of($candidate)->make(true);
    }

    public function  ElectoralArea(){
        /* $regions = ElectoralArea::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "electoralarea.*"
            )
            ->join('countries','countries.id','=','electoralarea.country_id')
            ->join('region','region.id','=','electoralarea.region_id')
            ->join('constituency','constituency.id','=','electoralarea.constituency_id')
            ->get(); */
            $regions = Region::orderBy('name','asc')->get();
        return view('national.agent.ElectoralArea',compact('regions'));
    }
    public function electralAajax(Request $request){
        $regions = ElectoralArea::select(
            'countries.id as c_id',
            'countries.name as country_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "electoralarea.*",
            DB::raw("(select sum(total_voters) from pollingstation where  pollingstation.electoralarea_id = electoralarea.id) as total_voters"),
            DB::raw("(select count(id) from pollingstation where  pollingstation.electoralarea_id = electoralarea.id) as total_polling")

        )
        ->join('countries','countries.id','=','electoralarea.country_id')
        ->join('region','region.id','=','electoralarea.region_id')
        ->join('constituency','constituency.id','=','electoralarea.constituency_id');
        //->leftJoin('pollingstation','pollingstation.electoralarea_id','=','electoralarea.id');

        if($request->input('region_id') != 'all')
            $regions = $regions ->where('electoralarea.region_id',$request->input('region_id'));
        if($request->input('constituency_id') != 'all')
            $regions = $regions ->where('electoralarea.constituency_id',$request->input('constituency_id'));
        return DataTables::of($regions)->make(true);

    }

    public function  PollingStation(){
        /* $regions = PollingStation::select(
                'countries.id as c_id',
                'countries.name as country_name',
                'region.name as region_name',
                "constituency.name as constituency_name",
                "electoralarea.name as ElectoralArea_name",
                "pollingstation.*"
            )
            ->join('countries','countries.id','=','pollingstation.country_id')
            ->join('region','region.id','=','pollingstation.region_id')
            ->join('constituency','constituency.id','=','pollingstation.constituency_id')
            ->join('electoralarea','electoralarea.id','=','pollingstation.electoralarea_id')
            ->get(); */
            $regions = Region::orderBy('name','asc')->get();
        return view('national.agent.Polling',compact('regions'));
    }
    public function pollingStationAajax(Request $request){
        $regions = PollingStation::select(
            'countries.id as c_id',
            'countries.name as country_name',
            'region.name as region_name',
            "constituency.name as constituency_name",
            "electoralarea.name as ElectoralArea_name",
            "pollingstation.*"
        )
        ->join('countries','countries.id','=','pollingstation.country_id')
        ->join('region','region.id','=','pollingstation.region_id')
        ->join('constituency','constituency.id','=','pollingstation.constituency_id')
        ->leftJoin('electoralarea','electoralarea.id','=','pollingstation.electoralarea_id');
        if($request->input('region_id') != 'all')
            $regions = $regions ->where('pollingstation.region_id',$request->input('region_id'));
        if($request->input('constituency_id') != 'all')
            $regions = $regions ->where('pollingstation.constituency_id',$request->input('constituency_id'));

            if($request->input('electoralarea_id') != 'all')
            $regions = $regions ->where('pollingstation.electoralarea_id',$request->input('electoralarea_id'));
        return DataTables::of($regions)->make(true);
    }
    public function profile(){

        $user = User::select(
            'users.username',
            'users.secret',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name',
            'constituency.name as constituency_name'
        )
        ->where('users.id', Auth::user()->id)
        ->join('user_type','user_type.id','=','users.user_type_id')
        ->leftJoin('region','region.id','=','users.region_id')
        ->leftJoin('constituency','constituency.id','=','users.constituency_id')
        ->first();
        //dd($user->toArray());
        return view('national.home.profile',compact('user'));
    }

    public function result(Request $request, $id){
        $election = ElectionType::findOrFail($id);
        $regions = Region::orderBy('name', 'asc')->get();
        $startupContext = $this->resultAnalytics()->getStartupFilterData((int) $id, $this->requestStartupId($request));
        $initialBreakdown = $this->resultAnalytics()->getFilteredBreakdown([
            'election_type_id' => $id,
            'election_start_up_id' => $startupContext['selected_id'] ?? 'all',
        ]);

        return view("national.home.result", [
            'election' => $election,
            'id' => $id,
            'details' => $startupContext['options'],
            'regions' => $regions,
            'selectedStartupId' => $startupContext['selected_id'],
            'selectedStartupName' => $startupContext['selected_name'],
            'initialBreakdown' => $initialBreakdown,
        ]);
    }
    public function allResultAjax(Request $request){
        return $this->resultAnalytics()->getFilteredBreakdown($request->all());
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

    protected function resultAnalytics(): NationalResultAnalyticsService
    {
        return app(NationalResultAnalyticsService::class);
    }

    protected function requestStartupId(Request $request): ?int
    {
        $startupId = $request->query('startup_id', $request->input('startup_id'));

        return is_numeric($startupId) ? (int) $startupId : null;
    }
}

