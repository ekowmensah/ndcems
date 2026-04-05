<?php

namespace App\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use App\SuperAdmin;
use Validator;
use Hash;
use App\Model\ElectionResult;
use App\Model\ElectionType;
use DB;
use App\Model\Region;
use App\Model\Constituency;
use App\Model\ElectionStartupDetail;
use App\Model\ElectoralArea;
use App\Model\PollingStation;
use App\Model\UserType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Model\PartyElectionResult;

class HomeController extends Controller
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
    public function index()
    {
        $total_region = Region::all()->count();
        $total_constituency = Constituency::all()->count();
        $total_electoralArea = ElectoralArea::all()->count();
        $total_pollingStation = PollingStation::all()->count();
        $total_voters = PollingStation::all()->sum('total_voters');
        $userTypeDetail = UserType::select(
            'user_type.name',
            DB::raw("(select count(users.id) from users where users.user_type_id = user_type.id) as user_type__count")

        )->get();
        //dd($userTypeDetail->toArray());
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
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
            ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->where('election_result.election_type_id', $NewElectionTypes[0]['id'])
            ->orderBy('candidates.ordering_position','ASC');
        $electionResults = $electionResult->get();
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));
        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->election_result), "y" => (($electionResult->election_result * 100) / $total));
        }
        $dataPoints = array_unique($dataPoints, SORT_REGULAR);
        $dataPoints1 = [];
        foreach ($dataPoints as $dataPoint) {
            $dataPoints1[] = $dataPoint;
        }
        $dataPoints = $dataPoints1;

        return view('admin.dashboard.index', compact('userTypeDetail', 'total_voters', 'total_pollingStation', 'total_electoralArea', 'total_constituency', 'dataPoints', 'total_region'));
    }
    public function presidentialResultAjax()
    {
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
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
            ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->where('election_result.election_type_id', $NewElectionTypes[0]['id'])
            ->orderBy('candidates.ordering_position','ASC');
        $electionResults = $electionResult->get();
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->election_result), "y" => (($electionResult->election_result * 100) / $total));
        }
        $dataPoints = array_unique($dataPoints, SORT_REGULAR);
        $dataPoints1 = [];
        foreach ($dataPoints as $dataPoint) {
            $dataPoints1[] = $dataPoint;
        }
        if (count($dataPoints1) <= 0) {
            $dataPoints1 = array(
                array("label" => "No Results", "y" => 00.00)
            );
        }
        $dataPoints = $dataPoints1;
        return $dataPoints;
    }
    public function admin()
    {
        $superAdmins = SuperAdmin::all();
        return view('admin.dashboard.admin', compact('superAdmins'));
    }

    public function newAdmin()
    {

        return view('admin.dashboard.newAdmin');
    }
    public function editAdmin($id)
    {
        $superAdmin = SuperAdmin::find($id);
        return view('admin.dashboard.editAdmin', compact('superAdmin'));
    }
    public function deleteAdmin($id, Request $request)
    {
        $superAdmin = SuperAdmin::find($id);
        $superAdmin->delete();
        $request->session()->flash('message', 'Admin user removed parmenently..!');
        return redirect()->back();
    }
    public function editAdminPost($id, Request $request)
    {
        $superAdmin = SuperAdmin::find($id);
        $data = $request->all();
        if ($data['password']) {
            $validation = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:6']
            ]);
        } else {
            $validation = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255']
            ]);
        }


        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation)
                ->withInput();
        } else {


            $superAdmin->name = $data['name'];
            if ($data['password']) {
                $data['password'] = Hash::make($data['password']);
                $superAdmin->password = $data['password'];
            }
            $superAdmin->email = $data['email'];
            $superAdmin->save();
            $request->session()->flash('message', 'Admin user updated!');
            return redirect()->back();
        }
    }

    public function newAdminPost(Request $request)
    {

        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:super_admin']
        ]);

        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation)
                ->withInput();
        } else {
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);



            SuperAdmin::create($data);
            $request->session()->flash('message', 'Admin User Created!');
            return redirect(route('SuperAdmin.admin'));
        }



    }
    public function parliamentaryResult()
    {
        $regions = Region::orderBy('name', 'asc')->get();

        return view("admin.dashboard.parliamentaryResult", compact('regions'));
    }
    public function constituencyView($id)
    {
        $constituency_detail = Constituency::find($id);
        $regions = Region::find($constituency_detail->region_id);
        $constituencies = Constituency::where('region_id', $regions->id)->get();
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
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
            ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->where('election_result.election_type_id', $NewElectionTypes[1]['id'])
            ->where('election_result.constituency_id', $id);
        $electionResults = $electionResult->get();
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));

        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->election_result), "y" => (($electionResult->election_result * 100) / $total));
        }
        $dataPoints = array_unique($dataPoints, SORT_REGULAR);

        $dataPoint1 = [];
        foreach ($dataPoints as $dataPoint) {
            $dataPoint1[] = $dataPoint;
        }

        if (count($dataPoint1) <= 0) {
            $dataPoint1 = array(
                array("label" => "No Results", "y" => 00.00)
            );
        }
        $dataPoints = $dataPoint1;
        return view("admin.dashboard.parliamentaryResultView", compact('constituency_detail', 'dataPoints', 'constituencies'));
    }
    public function result($id)
    {
        $regions = Region::all();
        $election = ElectionType::find($id);
        $details = ElectionStartupDetail::where('election_type_id', $id)->get();
        return view("admin.dashboard.result", compact('election', 'id', 'details', 'regions'));

    }
    public function allResultAjax(Request $request)
    {
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
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
            "election_result.obtained_votes as election_result"

            //DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.election_result_id = election_result.id) as election_result"),
            /* DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result
            where party_election_result.candidate_id = candidates.id
            and party_election_result.election_result_id = election_result.id
            ) as party_election_result_obtained_vote") */
            //

        )
           ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->orderBy('candidates.ordering_position','ASC');
        if ($request->input('election_type_id') != "all") {
                $electionResult = $electionResult->where('election_result.election_type_id', $request->input('election_type_id'));
        }
        if ($request->input('election_start_up_id') != "all") {
            $electionResult = $electionResult->where('election_result.election_start_up_id', $request->input('election_start_up_id'));
            /* $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        }

        if ($request->input('region_id') != "all") {
            /* $electionResult = ElectionResult::select(
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
                "election_result.obtained_votes as election_result"
                )
               ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
                ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
                ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id'); */
            $electionResult = $electionResult->where('party_election_result.region_id', $request->input('region_id'));
            /* $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->where('party_election_result.region_id', $request->input('region_id'))
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        }
        if ($request->input('constituency_id') != "all") {

            $electionResult = $electionResult->where('party_election_result.constituency_id', $request->input('constituency_id'));
            /* $electionResult = $electionResult->groupBy('political_party.party_initial')
        ->where('party_election_result.constituency_id', $request->input('constituency_id'))
        ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        //dd($electionResult->toArray());
    }
    if($request->input('electoralarea_id') != "all"){
        $electionResult = $electionResult->where('election_result.electoral_area_id',$request->input('electoralarea_id'));

      }
        if ($request->input('polling_station_id') != "all") {
            /* $electionResult = ElectionResult::select(
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
                "election_result.obtained_votes as election_result"
                )
               ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
                ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
                ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id'); */
            $electionResult = $electionResult->where('party_election_result.polling_station_id', $request->input('polling_station_id'));
        }


        $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
        $electionResults = $electionResult->get();
        //dd($electionResults->toArray());
        $total = array_sum(array_column($electionResults->toArray(), 'party_election_result_obtained_vote'));
        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->party_election_result_obtained_vote), "y" => (($electionResult->party_election_result_obtained_vote * 100) / $total));
        }

        $dataPoints = array_unique($dataPoints, SORT_REGULAR);
        $dataPoints1 = [];
        foreach ($dataPoints as $dataPoint) {
            $dataPoints1[] = $dataPoint;
        }
        if (count($dataPoints1) <= 0) {
            $dataPoints1 = array(
                array("label" => "No Results", "y" => 00.00)
            );
        }
        $dataPoints = $dataPoints1;
        return $dataPoints;
    }
    public function resultReport($id){
        $regions = Region::all();
        $election = ElectionType::find($id);
        $details = ElectionStartupDetail::where('election_type_id', $id)->get();
        return view("admin.dashboard.resultReport", compact('election', 'id', 'details', 'regions'));
    }
    public function resultReportPost($id,Request $request){
        $election = ElectionType::find($id);
        $electionTypes = ElectionType::all();
        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
        }
        $electionResult = Region::select(
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
            "election_result.obtained_votes as election_result",
            "election_type.name as election_type_name",
            "election_startup_detail.election_name",
            'region.name as region_name',
            "constituency.name as constituency_name",
            "electoralarea.name as ElectoralArea_name",
            "pollingstation.name as PollingStation_name",
            "pollingstation.id as PollingStation_id",
            "pollingstation.polling_station_id as PollingStation_code"
        )
            ->join('election_result','region.id','=','election_result.region_id')
            ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->join('election_type','election_type.id','=','election_result.election_type_id')
            ->join('election_startup_detail','election_startup_detail.id','=','election_result.election_start_up_id')
            ->join('pollingstation','pollingstation.id','=','election_result.polling_station_id')
            ->join('constituency','constituency.id','=','election_result.constituency_id')
            ->join('electoralarea','electoralarea.id','=','election_result.electoral_area_id')
            ->orderBy('candidates.ordering_position','ASC');

        if ($request->input('election_type_id') ) {
                $electionResult = $electionResult->where('election_result.election_type_id', $request->input('election_type_id'));
            }
        if ($request->input('election_start_up_id') ) {
            $electionResult = $electionResult->where('election_result.election_start_up_id', $request->input('election_start_up_id'));
        }
        if ($request->input('region_id') && $request->input('region_id')!='all') {
            $electionResult = $electionResult->where('party_election_result.region_id', $request->input('region_id'));
        }
        if ($request->input('constituency_id') && $request->input('constituency_id')!='all' ) {
            $electionResult = $electionResult->where('party_election_result.constituency_id', $request->input('constituency_id'));
        }
        if($request->input('electoralarea_id') && $request->input('electoralarea_id')!='all' ){
            $electionResult = $electionResult->where('election_result.electoral_area_id',$request->input('electoralarea_id'));
        }
        if ($request->input('polling_station_id') && $request->input('polling_station_id')!='all' ) {
            $electionResult = $electionResult->where('party_election_result.polling_station_id', $request->input('polling_station_id'));
        }
        /* $electionResult3 = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote'); */
        $electionResults = $electionResult->orderBy('pollingstation.id')->get();
        $electionResults2 = $electionResult->groupBy('pollingstation.id')->get();
        $PollingStation = @$electionResults->toArray();
        //dd($PollingStation);
        //====================== new sheet ====================
        //=====================================================

        $styleArray = array(
            'font' => array(
                'bold' => true
                )
        );
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheet=$spreadsheet->createSheet();
        $sheet->setTitle('Result');
        $sheet->setCellValue('E1',"RESULTS REPORT SUMMARY SHEET");
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getStyle('E1')->applyFromArray(array(
            'font' => array(
                'bold'  => true,
                //'color' => array('rgb' => 'FF0000'),
                'size'  => 14,
                'name'  => 'Verdana'
                )
        ));
        $sheet->setCellValue('A2',"Election");
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getStyle('A2')->applyFromArray($styleArray);
        $sheet->getStyle('B2')->applyFromArray($styleArray);
        $sheet->getStyle('C2')->applyFromArray($styleArray);
        $sheet->getStyle('D2')->applyFromArray($styleArray);
        $sheet->getStyle('E2')->applyFromArray($styleArray);
        $sheet->getStyle('F2')->applyFromArray($styleArray);
        $sheet->getStyle('G2')->applyFromArray($styleArray);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->setCellValue('B2',"Election Type");
        $sheet->setCellValue('C2',"Region");
        $sheet->setCellValue('D2',"Constituency");
        $sheet->setCellValue('E2',"Electoral Area");
        $sheet->setCellValue('F2',"Polling Station");
        $sheet->setCellValue('G2',"Polling Station Code");
        $count=3;
        // /dd($electionResults2->toArray());
        foreach ($electionResults2 as $key => $electionResult) {

            //echo $PollingStation[$key]['PollingStation_id'];
            //echo $PollingStation[$key]['first_name']."<br>";

            $sheet->setCellValue('A'.$count,$electionResult->election_type_name);
            $sheet->setCellValue('B'.$count,$electionResult->election_name);
            $sheet->setCellValue('C'.$count,$electionResult->region_name);
            $sheet->setCellValue('D'.$count,$electionResult->constituency_name);
            $sheet->setCellValue('E'.$count,$electionResult->ElectoralArea_name);
            $sheet->setCellValue('F'.$count,$electionResult->PollingStation_name);
            $sheet->setCellValue('G'.$count,$electionResult->PollingStation_code);

            /* $sheet->setCellValue('A'.$count,$PollingStation[$key]['election_type_name']);
            $sheet->setCellValue('B'.$count,$PollingStation[$key]['election_name']);
            $sheet->setCellValue('C'.$count,$PollingStation[$key]['region_name']);
            $sheet->setCellValue('D'.$count,$PollingStation[$key]['constituency_name']);
            $sheet->setCellValue('E'.$count,$PollingStation[$key]['ElectoralArea_name']);
            $sheet->setCellValue('F'.$count,$PollingStation[$key]['PollingStation_name']);
            $sheet->setCellValue('G'.$count,$PollingStation[$key]['PollingStation_code']); */


            $index = 8;
            $partyResults = PartyElectionResult::select(
                "candidates.first_name",
                "candidates.last_name",
                "political_party.party_initial",
                "political_party.id as political_party_id",
                "party_election_result.obtained_vote as party_election_result_obtained_vote"
            )
                ->where('party_election_result.polling_station_id',$electionResult->PollingStation_id)
                ->where('party_election_result.election_result_id',$electionResult->id)

                ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
                ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
                ->get();
                $runONce = false;
            foreach ($partyResults as $key => $partyResults) {
                //dd($partyResults->toArray());
                if($count+1==4 )
                    $sheet->setCellValueByColumnAndRow($index, $count-1, $PollingStation[$key]['first_name']." ".$PollingStation[$key]['last_name']." (".$PollingStation[$key]['party_initial'].")");

                $sheet->setCellValueByColumnAndRow($index, $count, $partyResults->party_election_result_obtained_vote);
                $index++;

            }
            $runONce = true;
            if($count+1==4 )

            $sheet->setCellValueByColumnAndRow($index, $count-1, "REJECTED BALLOTS");

            $sheet->setCellValueByColumnAndRow($index, $count, $PollingStation[$key]['total_rejected_ballot']);

            //$sheet->setCellValue('G'.$count,$PollingStation[$key]['PollingStation_code']);

            //dd($partyResult->toArray());
    //        $sheet->setCellValue('G2',"Polling Station Code");

                //echo $electionResult->PollingStation_id."\n";
                $count++;

        }
        //dd($electionResults->toArray());
        //====================== new sheet end ====================
        //=================================================
        /* $styleArray = array(
            'font' => array(
                'bold' => true
                )
        );
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        $sheet=$spreadsheet->createSheet();
        $sheet->setTitle('Result');

        $sheet->setCellValue('D1',"RESULTS REPORT SUMMARY SHEET");
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getStyle('D1')->applyFromArray(array(
            'font' => array(
                'bold'  => true,
                //'color' => array('rgb' => 'FF0000'),
                'size'  => 35,
                'name'  => 'Verdana'
                )
        ));

        $sheet->setCellValue('A2',"Election");
        $sheet->getColumnDimension('A')->setWidth(20);

        $sheet->getStyle('A2')->applyFromArray($styleArray);
        $sheet->getStyle('B2')->applyFromArray($styleArray);
        $sheet->getStyle('C2')->applyFromArray($styleArray);
        $sheet->getStyle('D2')->applyFromArray($styleArray);
        $sheet->getStyle('E2')->applyFromArray($styleArray);
        $sheet->getStyle('F2')->applyFromArray($styleArray);
        $sheet->getStyle('G2')->applyFromArray($styleArray);
        $sheet->getStyle('I2')->applyFromArray($styleArray);
        $sheet->getStyle('J2')->applyFromArray($styleArray);

        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(20);

        $sheet->setCellValue('B2',"Election Type");
        $sheet->setCellValue('C2',"Region");
        $sheet->setCellValue('D2',"Constituency");
        $sheet->setCellValue('E2',"Electoral Area");
        $sheet->setCellValue('F2',"Polling Station");
        $sheet->setCellValue('G2',"Polling Station Code");
        $sheet->setCellValue('H2',"Candidate");
        $sheet->setCellValue('I2',"Political Party");
        $sheet->setCellValue('J2',"Votes Obtained");
        $count=3;
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
        } */

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Result'.$election->name.'".xlsx ');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}

