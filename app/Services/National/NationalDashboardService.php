<?php

namespace App\Services\National;

use App\Model\ElectionStartupDetail;
use App\Model\ElectionType;
use App\Model\PollingStation;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NationalDashboardService
{
    public function getPresidentialElectionTypeId(): ?int
    {
        return $this->resolveElectionTypeId(['president'], 0);
    }

    public function getConstituencyElectionTypeId(): ?int
    {
        return $this->resolveElectionTypeId(['parliament', 'constituency'], 1);
    }

    public function getDashboardData(): array
    {
        $summary = $this->getSummaryStats();

        return [
            'summary' => $summary,
            'chartDataPoints' => $this->getPresidentialChartData(),
            'regionalPerformance' => $this->getRegionalPerformance(),
            'activeElection' => $this->getActiveElection(),
            'lastUpdatedAt' => $this->formatDateTime($summary['last_result_at']),
        ];
    }

    public function getPresidentialChartData(): array
    {
        $electionTypeId = $this->getPresidentialElectionTypeId();

        $partyTotals = DB::table('party_election_result')
            ->join('election_result', 'election_result.id', '=', 'party_election_result.election_result_id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->select(
                'political_party.party_initial',
                'political_party.name as party_name',
                DB::raw('SUM(party_election_result.obtained_vote) as total_votes')
            )
            ->when($electionTypeId, function ($query, $resolvedElectionTypeId) {
                return $query->where('election_result.election_type_id', $resolvedElectionTypeId);
            })
            ->groupBy('political_party.id', 'political_party.party_initial', 'political_party.name')
            ->orderByDesc('total_votes')
            ->get();

        return $this->formatChartData($partyTotals, 'party_initial');
    }

    public function getRegionalPerformance(int $limit = 6): array
    {
        $reportingQuery = DB::table('election_result')
            ->select(
                'region_id',
                DB::raw('COUNT(DISTINCT polling_station_id) as reporting_stations'),
                DB::raw('SUM(total_ballot) as total_ballots'),
                DB::raw('SUM(total_rejected_ballot) as total_rejected_ballots')
            )
            ->groupBy('region_id');

        $pollingQuery = DB::table('pollingstation')
            ->select(
                'region_id',
                DB::raw('COUNT(*) as total_polling_stations'),
                DB::raw('SUM(total_voters) as total_voters')
            )
            ->groupBy('region_id');

        return DB::table('region')
            ->leftJoinSub($reportingQuery, 'reporting', function ($join) {
                $join->on('reporting.region_id', '=', 'region.id');
            })
            ->leftJoinSub($pollingQuery, 'polling', function ($join) {
                $join->on('polling.region_id', '=', 'region.id');
            })
            ->select(
                'region.id',
                'region.name',
                DB::raw('COALESCE(polling.total_polling_stations, 0) as total_polling_stations'),
                DB::raw('COALESCE(reporting.reporting_stations, 0) as reporting_stations'),
                DB::raw('COALESCE(reporting.total_ballots, 0) as total_ballots'),
                DB::raw('COALESCE(reporting.total_rejected_ballots, 0) as total_rejected_ballots'),
                DB::raw('COALESCE(polling.total_voters, 0) as total_voters')
            )
            ->orderBy('region.name')
            ->get()
            ->map(function ($region) {
                $totalPollingStations = (int) $region->total_polling_stations;
                $reportingStations = (int) $region->reporting_stations;
                $reportingPercentage = $totalPollingStations > 0
                    ? round(($reportingStations / $totalPollingStations) * 100, 1)
                    : 0.0;

                return [
                    'id' => (int) $region->id,
                    'name' => $region->name,
                    'total_polling_stations' => $totalPollingStations,
                    'reporting_stations' => $reportingStations,
                    'reporting_percentage' => $reportingPercentage,
                    'total_ballots' => (int) $region->total_ballots,
                    'total_rejected_ballots' => (int) $region->total_rejected_ballots,
                    'total_voters' => (int) $region->total_voters,
                ];
            })
            ->sortByDesc('reporting_percentage')
            ->take($limit)
            ->values()
            ->all();
    }

    protected function getSummaryStats(): array
    {
        $totalPollingStations = (int) PollingStation::count();
        $reportingStations = (int) DB::table('election_result')->distinct()->count('polling_station_id');
        $totalBallots = (int) DB::table('election_result')->sum('total_ballot');
        $totalRejectedBallots = (int) DB::table('election_result')->sum('total_rejected_ballot');
        $totalResults = (int) DB::table('election_result')->count();
        $reportingPercentage = $totalPollingStations > 0
            ? round(($reportingStations / $totalPollingStations) * 100, 1)
            : 0.0;
        $rejectedBallotRate = $totalBallots > 0
            ? round(($totalRejectedBallots / $totalBallots) * 100, 1)
            : 0.0;

        return [
            'total_polling_stations' => $totalPollingStations,
            'reporting_stations' => $reportingStations,
            'reporting_percentage' => $reportingPercentage,
            'total_ballots' => $totalBallots,
            'total_rejected_ballots' => $totalRejectedBallots,
            'rejected_ballot_rate' => $rejectedBallotRate,
            'total_results' => $totalResults,
            'last_result_at' => DB::table('election_result')->max('updated_at'),
        ];
    }

    protected function getActiveElection(): array
    {
        $activeElection = ElectionStartupDetail::query()
            ->orderByDesc('status')
            ->orderByDesc('start')
            ->first();

        if (!$activeElection) {
            return [
                'name' => 'No active election startup',
                'date_range' => 'Awaiting configuration',
                'status' => 'Inactive',
            ];
        }

        return [
            'name' => $activeElection->election_name,
            'date_range' => trim($this->formatDateTime($activeElection->start).' - '.$this->formatDateTime($activeElection->end)),
            'status' => (string) $activeElection->status === '1' ? 'Active' : 'Configured',
        ];
    }

    protected function resolveElectionTypeId(array $keywords, int $fallbackIndex = 0): ?int
    {
        $electionTypes = ElectionType::query()->get(['id', 'name'])->values();

        $matchedType = $electionTypes->first(function ($electionType) use ($keywords) {
            return Str::contains(Str::lower($electionType->name), $keywords);
        });

        if ($matchedType) {
            return (int) $matchedType->id;
        }

        $fallbackType = $electionTypes->get($fallbackIndex) ?: $electionTypes->first();

        return $fallbackType ? (int) $fallbackType->id : null;
    }

    protected function formatChartData(Collection $partyTotals, string $labelKey): array
    {
        $totalVotes = (int) $partyTotals->sum('total_votes');

        if ($totalVotes <= 0 || $partyTotals->isEmpty()) {
            return [
                ['label' => 'No Results', 'y' => 0.0],
            ];
        }

        return $partyTotals->map(function ($partyTotal) use ($totalVotes, $labelKey) {
            $votes = (int) $partyTotal->total_votes;

            return [
                'label' => $partyTotal->{$labelKey}.' - '.number_format($votes),
                'y' => round(($votes / $totalVotes) * 100, 2),
            ];
        })->values()->all();
    }

    protected function formatDateTime($dateTime): string
    {
        if (!$dateTime) {
            return 'Not available';
        }

        return Carbon::parse($dateTime)->format('M d, Y g:i A');
    }
}
