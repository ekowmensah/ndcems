<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\PartyElectionResult;
use App\Model\ElectionResult;
use App\Model\ElectionType;
use DB;
use App\Model\ElectionStartupDetail;
use Illuminate\Support\Facades\Route;
use App\Model\Region;
use App\Model\Constituency;
use App\Model\ElectoralArea;
use App\Model\PollingStation;

class PublicController extends Controller
{

    public function notify_me(Request $request){
        $path = storage_path('twilio.txt');
        $data = json_encode($request->all());
        file_put_contents($path,$data);
    }
    public function parliament($id=false)
    {
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
        }

        /////    winner start
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
            ->orderBy('election_result', 'desc');
            $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
        $electionResults = $electionResult->take(2)->get();


        $total = array_sum(array_column(@$electionResults->toArray(), 'party_election_result_obtained_vote'));
        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->party_election_result_obtained_vote), "y" => (($electionResult->party_election_result_obtained_vote * 100) / $total));
        }
        if (count($dataPoints) <= 0) {
            $dataPoints = array(
                array("label" => "No Results", "y" => 00.00),
                array("label" => "No Results", "y" => 00.00)
            );
        }
        /////    winner end
        /////    Regional =={{{{

            $electionResult = ElectionResult::select(
                "party_election_result.obtained_vote as party_election_result_obtained_vote",
                "election_result.id",
                "political_party.party_initial",
                "political_party.name as political_party_name",
                "political_party.id as political_party_id",
                "candidates.first_name",
                "candidates.id as candidate_id",
                "candidates.last_name",
                "candidates.photo",
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
                ->orderBy('election_result', 'desc');
                $electionResult = $electionResult->groupBy('political_party.party_initial')
                ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
                if($id)

                $electionResult = $electionResult->where('election_result.election_start_up_id', $id);
           $allElectionResults = $electionResult->get();
            $_total = array_sum(array_column(@$allElectionResults->toArray(), 'party_election_result_obtained_vote'));
            foreach ($allElectionResults as $electionResult) {
                $electionResult->percentage = (($electionResult->party_election_result_obtained_vote * 100) / $_total);
            }
            $colors = array('#0000FF','#98FB98','red');

            $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
                )
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("status",1)
            ->where("election_type_id",$NewElectionTypes[1]['id'])
            ->get();
            //dd($allElectionResults->toArray());
            $newElectionType = $NewElectionTypes[1]['id'];
            $regions = Region::all();

            $polling_count = ElectionResult::select('polling_station_id')
                ->where('election_result.election_type_id', $NewElectionTypes[1]['id'])
                ->where('election_result.verify_by_constituency', 1);
            if($id)
                $polling_count = $polling_count->where('election_result.election_start_up_id', $id);
                $polling_count = $polling_count->count();
                $all_polling_count = ElectionResult::select('polling_station_id')
                    ->where('election_result.election_type_id', $NewElectionTypes[1]['id'])->count();

            $all_polling_count = PollingStation::select('polling_station_id')->count();
        return view('public.parliament',compact('polling_count','all_polling_count','regions','dataPoints','allElectionResults','colors','electionStartupDetail','id','newElectionType'));
    }
    public function president($id=false)
    {
        $electionTypes = ElectionType::all();

        $NewElectionTypes = [];
        foreach ($electionTypes->toArray() as $i => $value) {
            $data = [
                "index" => $i
            ];
            $NewElectionTypes[] = array_merge($value, array_merge($value, $data));
        }

        /////    winner start
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
            DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

            )
           ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->where('election_result.election_type_id', $NewElectionTypes[0]['id'])
            ->orderBy('election_result', 'desc');
            $electionResult = $electionResult->groupBy('political_party.party_initial')
            ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
        $electionResults = $electionResult->take(2)->get();


        $total = array_sum(array_column(@$electionResults->toArray(), 'party_election_result_obtained_vote'));
        $dataPoints = [];
        foreach ($electionResults as $electionResult) {
            $dataPoints[] = array("label" => $electionResult->party_initial . "  -  " . number_format($electionResult->party_election_result_obtained_vote), "y" => (($electionResult->party_election_result_obtained_vote * 100) / $total));
        }
        if (count($dataPoints) <= 0) {
            $dataPoints = array(
                array("label" => "No Results", "y" => 00.00),
                array("label" => "No Results", "y" => 00.00)
            );
        } */
        /////    winner end
        /////    Regional =={{{{
            $polling_count = ElectionResult::select('polling_station_id')
                ->where('election_result.election_type_id', $NewElectionTypes[0]['id'])
                ->where('election_result.verify_by_constituency', 1);
            if($id)
                $polling_count = $polling_count->where('election_result.election_start_up_id', $id);
                $polling_count = $polling_count->count();
                $all_polling_count = ElectionResult::select('polling_station_id')
                    ->where('election_result.election_type_id', $NewElectionTypes[0]['id'])->count();

            $electionResult = ElectionResult::select(
                "party_election_result.obtained_vote as party_election_result_obtained_vote",
                "election_result.id",
                "election_result.polling_station_id",
                "political_party.party_initial",
                "political_party.name as political_party_name",
                "political_party.id as political_party_id",
                "candidates.first_name",
                "candidates.id as candidate_id",
                "candidates.last_name",
                "candidates.photo",
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

                ->orderBy('election_result', 'desc');
                //$_electionResult = $electionResult->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
                $electionResult = $electionResult->groupBy('political_party.party_initial')->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
                if($id)
                    $electionResult = $electionResult->where('election_result.election_start_up_id', $id);
            $allElectionResults = $electionResult->get();

            $_total = array_sum(array_column(@$allElectionResults->toArray(), 'party_election_result_obtained_vote'));
            foreach ($allElectionResults as $electionResult) {
                $electionResult->percentage = (($electionResult->party_election_result_obtained_vote * 100) / $_total);
            }
            $colors = array('#0000FF','#98FB98','red');

            $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
                )
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("status",1)
            ->where("election_type_id",$NewElectionTypes[0]['id'])
            ->get();
            $newElectionType = $NewElectionTypes[0]['id'];
            $regions = Region::all();

        return view('public.president',compact('polling_count','all_polling_count','regions','allElectionResults','colors','electionStartupDetail','id','newElectionType'));
    }
    public function ajaxResult(Request $request){
        if($request->input('election_type_id'))
            $id = $request->input('election_type_id');
            $_newElectionType = $request->input('newElectionType');
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
                    "political_party.name as political_party_name",
                    "political_party.id as political_party_id",
                    "candidates.first_name",
                    "candidates.id as candidate_id",
                    "candidates.last_name",
                    "candidates.photo",
                    "election_result.total_ballot",
                    "election_result.total_rejected_ballot",
                    "election_result.election_start_up_id",
                    "election_result.obtained_votes as obtained_votes",
                    DB::raw("(select sum(party_election_result.obtained_vote) from party_election_result where party_election_result.candidate_id = candidates.id) as election_result")

                )
               ->join('party_election_result', 'party_election_result.election_result_id', '=', 'election_result.id')
                ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
                ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
                ->where('election_result.election_type_id', $_newElectionType)

                ->orderBy('election_result', 'desc');
                if ($request->input('election_start_up_id') != "all") {
                    $electionResult = $electionResult->where('election_result.election_start_up_id', $request->input('election_start_up_id'));
                }
                if ($request->input('region_id') != "all") {
                    $electionResult = $electionResult->where('party_election_result.region_id', $request->input('region_id'));
                }
                if ($request->input('constituency_id') != "all") {
                    $electionResult = $electionResult->where('party_election_result.constituency_id', $request->input('constituency_id'));
                }
                if($request->input('electoralarea_id') != "all"){
                    $electionResult = $electionResult->where('election_result.electoral_area_id',$request->input('electoralarea_id'));
                }
                if ($request->input('polling_station_id') != "all") {
                    $electionResult = $electionResult->where('party_election_result.polling_station_id', $request->input('polling_station_id'));
                }
                $electionResult = $electionResult->groupBy('political_party.party_initial')
                ->selectRaw('sum(party_election_result.obtained_vote) as party_election_result_obtained_vote');
                if(isset($id))

                $electionResult = $electionResult->where('election_result.election_start_up_id', $id);
            $allElectionResults = $electionResult->get();
            $_total = array_sum(array_column(@$allElectionResults->toArray(), 'party_election_result_obtained_vote'));
            foreach ($allElectionResults as $electionResult) {
                $electionResult->percentage = (($electionResult->party_election_result_obtained_vote * 100) / $_total);
            }
            $colors = array('#0000FF','#98FB98','red');

            $electionStartupDetail = ElectionStartupDetail::select(
                'election_type.name',
                'election_startup_detail.*'
                )
            ->join('election_type','election_type.id','=','election_startup_detail.election_type_id')
            ->where("status",1)
            ->where("election_type_id",$_newElectionType)
            ->get();

               // return $allElectionResults->toArray();

               $value = $allElectionResults->toArray();
               //$value1 =  $this->aasort($value,"party_election_result_obtained_vote");
                //return usort($allElectionResults->toArray(), 'sortByOrder');
                $value1 = $this->aasort($value,"party_election_result_obtained_vote");

                $ret = array();
                foreach ($value1 as $ii => $va)
                    $ret[]=$value1[$ii];

                $last_To_start = array_reverse($ret, true);
                $ret = array();
                foreach ($last_To_start as $ii => $va)
                    $ret[]=$last_To_start[$ii];

               return $ret;
        return view('public.index',compact('allElectionResults','colors','electionStartupDetail','id'));
    }
    public function  getConstituency(Request $request){
        $data = $request->all();
        $countries = Constituency::where('region_id',$data['region_id'])->get();
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
    public function ajaxCountResult(Request $request){
        $id = $request->input('election_type_id');
        $_newElectionType = $request->input('newElectionType');

        $polling_count = ElectionResult::select('polling_station_id')
                ->where('election_result.election_type_id', $_newElectionType)
                ->where('election_result.verify_by_constituency', 1);
            if($id)
                $polling_count = $polling_count->where('election_result.election_start_up_id', $id);
                $polling_count = $polling_count->count();
                $all_polling_count = ElectionResult::select('polling_station_id')
                    ->where('election_result.election_type_id', $_newElectionType)->count();
        return array(
            'polling_count'=>$polling_count,
            'all_polling_count'=>$all_polling_count
        );
    }
    function aasort ($array, $key) {
        $sorter=array();
        $ret=array();
        reset($array);
        foreach ($array as $ii => $va) {
            $sorter[$ii]=$va[$key];
        }
        asort($sorter);
        foreach ($sorter as $ii => $va) {
            $ret[$ii]=$array[$ii];
        }
        $array=$ret;
        return $array;
    }

}
