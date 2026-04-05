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
use App\Model\PollingStation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
    public function index(){
        $constituencyId = Auth::user()->constituency_id;
        $analytics = $this->buildDashboardAnalytics($constituencyId);
        return view("director.home.index", $analytics);
    }

    public function dashboardData()
    {
        $constituencyId = Auth::user()->constituency_id;
        $analytics = $this->buildDashboardAnalytics($constituencyId);
        return response()->json($analytics);
    }

    private function buildDashboardAnalytics($constituencyId)
    {
        $totalPollingStations = PollingStation::where('constituency_id', $constituencyId)->count();

        $overall = ElectionResult::selectRaw('
                COUNT(*) as submitted,
                SUM(CASE WHEN verify_by_constituency = 1 THEN 1 ELSE 0 END) as confirmed,
                SUM(CASE WHEN pink_sheet_path IS NOT NULL AND pink_sheet_path != "" THEN 1 ELSE 0 END) as pink_sheets,
                SUM(obtained_votes) as total_valid_votes,
                SUM(total_rejected_ballot) as total_rejected_votes
            ')
            ->where('constituency_id', $constituencyId)
            ->first();

        $submitted = (int) ($overall->submitted ?? 0);
        $confirmed = (int) ($overall->confirmed ?? 0);
        $pinkSheets = (int) ($overall->pink_sheets ?? 0);
        $pending = max($submitted - $confirmed, 0);
        $coverageRate = $totalPollingStations > 0 ? round(($submitted / $totalPollingStations) * 100, 2) : 0;
        $confirmationRate = $submitted > 0 ? round(($confirmed / $submitted) * 100, 2) : 0;
        $pinkSheetRate = $submitted > 0 ? round(($pinkSheets / $submitted) * 100, 2) : 0;

        $electionTypePerformance = ElectionResult::select(
                'election_type.id as election_type_id',
                'election_type.name as election_type_name',
                DB::raw('COUNT(election_result.id) as submissions'),
                DB::raw('SUM(CASE WHEN election_result.verify_by_constituency = 1 THEN 1 ELSE 0 END) as confirmations'),
                DB::raw('SUM(election_result.obtained_votes) as valid_votes')
            )
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->where('election_result.constituency_id', $constituencyId)
            ->groupBy('election_type.id', 'election_type.name')
            ->orderBy('election_type.name', 'asc')
            ->get()
            ->map(function ($row) use ($totalPollingStations) {
                $row->completion = $totalPollingStations > 0
                    ? round((((int) $row->submissions) / $totalPollingStations) * 100, 2)
                    : 0;
                $row->confirmation_rate = (int) $row->submissions > 0
                    ? round((((int) $row->confirmations) / ((int) $row->submissions)) * 100, 2)
                    : 0;
                return $row;
            });

        $activeStartups = ElectionStartupDetail::select(
                'election_startup_detail.id',
                'election_startup_detail.election_name',
                'election_type.name as election_type_name'
            )
            ->join('election_type', 'election_type.id', '=', 'election_startup_detail.election_type_id')
            ->where('election_startup_detail.status', 1)
            ->orderBy('election_startup_detail.id', 'desc')
            ->get();

        $startupCounts = ElectionResult::select(
                'election_start_up_id',
                DB::raw('COUNT(id) as submissions'),
                DB::raw('SUM(CASE WHEN verify_by_constituency = 1 THEN 1 ELSE 0 END) as confirmations')
            )
            ->where('constituency_id', $constituencyId)
            ->groupBy('election_start_up_id')
            ->get()
            ->keyBy('election_start_up_id');

        $startupPerformance = $activeStartups->map(function ($startup) use ($startupCounts, $totalPollingStations) {
            $counts = $startupCounts->get($startup->id);
            $submissions = (int) optional($counts)->submissions;
            $confirmations = (int) optional($counts)->confirmations;

            return (object) [
                'id' => $startup->id,
                'election_name' => $startup->election_name,
                'election_type_name' => $startup->election_type_name,
                'submissions' => $submissions,
                'confirmations' => $confirmations,
                'coverage' => $totalPollingStations > 0 ? round(($submissions / $totalPollingStations) * 100, 2) : 0,
                'confirmation_rate' => $submissions > 0 ? round(($confirmations / $submissions) * 100, 2) : 0,
            ];
        });

        $dashboardStats = [
            'submitted' => $submitted,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'pink_sheets' => $pinkSheets,
            'coverage_rate' => $coverageRate,
            'confirmation_rate' => $confirmationRate,
            'pink_sheet_rate' => $pinkSheetRate,
            'total_polling_stations' => $totalPollingStations,
            'total_valid_votes' => (int) ($overall->total_valid_votes ?? 0),
            'total_rejected_votes' => (int) ($overall->total_rejected_votes ?? 0),
        ];

        $statusBreakdown = [
            'confirmed' => $confirmed,
            'pending' => $pending,
            'pink_sheet_missing' => max($submitted - $pinkSheets, 0),
        ];

        $days = collect(range(13, 1))->map(function ($offset) {
            return Carbon::now()->subDays($offset)->format('Y-m-d');
        })->push(Carbon::now()->format('Y-m-d'));

        $dailyRows = ElectionResult::select(
                DB::raw('DATE(created_at) as day'),
                DB::raw('COUNT(id) as submissions'),
                DB::raw('SUM(CASE WHEN verify_by_constituency = 1 THEN 1 ELSE 0 END) as confirmations')
            )
            ->where('constituency_id', $constituencyId)
            ->where('created_at', '>=', Carbon::now()->subDays(14)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day', 'asc')
            ->get()
            ->keyBy('day');

        $trendDaily = [
            'labels' => [],
            'submissions' => [],
            'confirmations' => [],
        ];
        foreach ($days as $day) {
            $row = $dailyRows->get($day);
            $trendDaily['labels'][] = Carbon::parse($day)->format('d M');
            $trendDaily['submissions'][] = (int) optional($row)->submissions;
            $trendDaily['confirmations'][] = (int) optional($row)->confirmations;
        }

        $topPollingStations = ElectionResult::select(
                'pollingstation.name as polling_station_name',
                DB::raw('COUNT(election_result.id) as submissions'),
                DB::raw('SUM(election_result.obtained_votes) as valid_votes')
            )
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->where('election_result.constituency_id', $constituencyId)
            ->groupBy('pollingstation.id', 'pollingstation.name')
            ->orderByDesc('valid_votes')
            ->limit(8)
            ->get();

        $topStationsChart = [
            'labels' => $topPollingStations->pluck('polling_station_name')->values(),
            'valid_votes' => $topPollingStations->pluck('valid_votes')->map(function ($value) {
                return (int) $value;
            })->values(),
            'submissions' => $topPollingStations->pluck('submissions')->map(function ($value) {
                return (int) $value;
            })->values(),
        ];

        $parliamentaryRows = PartyElectionResult::select(
                DB::raw("CONCAT(candidates.first_name, ' ', candidates.last_name) as candidate_name"),
                'political_party.party_initial',
                'candidates.photo as candidate_photo',
                'political_party.logo as party_logo',
                DB::raw('SUM(party_election_result.obtained_vote) as total_votes')
            )
            ->join('election_result', 'election_result.id', '=', 'party_election_result.election_result_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->where('election_result.constituency_id', $constituencyId)
            ->where('election_result.verify_by_constituency', 1)
            ->where(function ($query) {
                $query->where('election_result.election_type_id', 2)
                    ->orWhereRaw('LOWER(election_type.name) LIKE ?', ['%parliament%']);
            })
            ->groupBy('candidates.id', 'candidates.first_name', 'candidates.last_name', 'candidates.photo', 'political_party.party_initial', 'political_party.logo')
            ->orderByDesc('total_votes')
            ->get();

        $parliamentaryResultsChart = [
            'labels' => $parliamentaryRows->map(function ($row) {
                return trim($row->candidate_name) . ' (' . $row->party_initial . ')';
            })->values(),
            'votes' => $parliamentaryRows->pluck('total_votes')->map(function ($value) {
                return (int) $value;
            })->values(),
            'rows' => $parliamentaryRows->map(function ($row) {
                return [
                    'candidate_name' => trim($row->candidate_name),
                    'party_initial' => $row->party_initial,
                    'votes' => (int) $row->total_votes,
                    'candidate_photo_url' => $row->candidate_photo ? asset('candidate_logo/' . $row->candidate_photo) : null,
                    'party_logo_url' => $row->party_logo ? asset('party_logo/' . $row->party_logo) : null,
                ];
            })->values(),
        ];

        return [
            'dashboardStats' => $dashboardStats,
            'electionTypePerformance' => $electionTypePerformance,
            'startupPerformance' => $startupPerformance,
            'statusBreakdown' => $statusBreakdown,
            'trendDaily' => $trendDaily,
            'topStationsChart' => $topStationsChart,
            'parliamentaryResultsChart' => $parliamentaryResultsChart,
        ];
    }
    public function pollingAgent(){
        return view("director.polling.index");
    }
}
