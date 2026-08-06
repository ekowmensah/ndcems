<?php

namespace App\Services\National;

use App\Model\Constituency;
use App\Model\ElectionStartupDetail;
use App\Model\PollingStation;
use App\Model\Region;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NationalResultAnalyticsService
{
    public function __construct(
        protected NationalDashboardService $dashboardService
    ) {
    }

    public function getPresidentialPageData(?int $startupId = null): array
    {
        $electionTypeId = $this->dashboardService->getPresidentialElectionTypeId();
        $startupContext = $this->getStartupContext($electionTypeId, $startupId);
        $summary = $this->buildNationalSummary($electionTypeId, $startupContext['selected_id']);

        return [
            'startupOptions' => $startupContext['options'],
            'selectedStartupId' => $startupContext['selected_id'],
            'selectedStartupName' => $startupContext['selected_name'],
            'summary' => $summary,
            'partyBreakdown' => $this->buildPartyBreakdown($electionTypeId, $startupContext['selected_id']),
            'regionalRows' => $this->getRegionalRows($electionTypeId, $startupContext['selected_id']),
            'lowCoverageWarning' => $this->buildCoverageWarning(
                $summary['reporting_regions'],
                (int) Region::count(),
                'presidential'
            ),
        ];
    }

    public function getParliamentaryPageData(?int $startupId = null): array
    {
        $electionTypeId = $this->dashboardService->getConstituencyElectionTypeId();
        $startupContext = $this->getStartupContext($electionTypeId, $startupId);
        $summary = $this->buildParliamentarySummary($electionTypeId, $startupContext['selected_id']);

        return [
            'startupOptions' => $startupContext['options'],
            'selectedStartupId' => $startupContext['selected_id'],
            'selectedStartupName' => $startupContext['selected_name'],
            'summary' => $summary,
            'partyBreakdown' => $this->buildPartyBreakdown($electionTypeId, $startupContext['selected_id']),
            'leadershipBreakdown' => $this->buildLeadershipBreakdown('constituency_id', $electionTypeId, $startupContext['selected_id']),
            'constituencyRows' => $this->getConstituencyRows($electionTypeId, $startupContext['selected_id']),
            'lowCoverageWarning' => $this->buildCoverageWarning(
                $summary['reporting_constituencies'],
                $summary['total_constituencies'],
                'parliamentary'
            ),
        ];
    }

    public function getStartupFilterData(?int $electionTypeId, ?int $requestedStartupId = null): array
    {
        return $this->getStartupContext($electionTypeId, $requestedStartupId);
    }

    public function getRegionalRows(?int $electionTypeId = null, ?int $startupId = null): Collection
    {
        $electionTypeId = $electionTypeId ?: $this->dashboardService->getPresidentialElectionTypeId();
        $results = $this->baseElectionResultQuery($electionTypeId, $startupId)
            ->select(
                'region_id',
                DB::raw('COUNT(DISTINCT polling_station_id) as reporting_stations'),
                DB::raw('COUNT(id) as submissions'),
                DB::raw('SUM(obtained_votes) as total_valid_votes'),
                DB::raw('SUM(total_rejected_ballot) as total_rejected_ballots'),
                DB::raw('SUM(total_ballot) as total_ballots'),
                DB::raw('MAX(updated_at) as updated_at')
            )
            ->groupBy('region_id')
            ->get()
            ->keyBy('region_id');

        $stations = PollingStation::query()
            ->select(
                'region_id',
                DB::raw('COUNT(id) as total_polling_stations'),
                DB::raw('SUM(total_voters) as total_voters')
            )
            ->groupBy('region_id')
            ->get()
            ->keyBy('region_id');

        $leaders = $this->buildLeadingPartyMap('region_id', $electionTypeId, $startupId);

        return Region::query()
            ->orderBy('name', 'asc')
            ->get(['id', 'name'])
            ->map(function ($region) use ($results, $stations, $leaders) {
                $resultRow = $results->get($region->id);
                $stationRow = $stations->get($region->id);
                $leader = $leaders->get($region->id, [
                    'party_initial' => 'N/A',
                    'party_name' => 'No verified results',
                    'votes' => 0,
                ]);

                $reportingStations = (int) optional($resultRow)->reporting_stations;
                $totalStations = (int) optional($stationRow)->total_polling_stations;
                $totalValidVotes = (int) optional($resultRow)->total_valid_votes;
                $totalRejectedBallots = (int) optional($resultRow)->total_rejected_ballots;
                $totalBallots = (int) optional($resultRow)->total_ballots;

                return [
                    'id' => (int) $region->id,
                    'region_id' => (int) $region->id,
                    'region_name' => $region->name,
                    'reporting_stations' => $reportingStations,
                    'submissions' => (int) optional($resultRow)->submissions,
                    'total_polling_stations' => $totalStations,
                    'coverage_percentage' => $totalStations > 0 ? round(($reportingStations / $totalStations) * 100, 2) : 0,
                    'total_valid_votes' => $totalValidVotes,
                    'total_rejected_ballots' => $totalRejectedBallots,
                    'total_ballots' => $totalBallots,
                    'rejected_rate' => $totalBallots > 0 ? round(($totalRejectedBallots / $totalBallots) * 100, 2) : 0,
                    'leading_party' => $leader['party_initial'],
                    'leading_party_name' => $leader['party_name'],
                    'leading_votes' => (int) $leader['votes'],
                    'total_voters' => (int) optional($stationRow)->total_voters,
                    'updated_at' => $this->formatDateTime(optional($resultRow)->updated_at),
                ];
            })
            ->values();
    }

    public function getConstituencyRows(?int $electionTypeId = null, ?int $startupId = null): Collection
    {
        $electionTypeId = $electionTypeId ?: $this->dashboardService->getConstituencyElectionTypeId();
        $results = $this->baseElectionResultQuery($electionTypeId, $startupId)
            ->select(
                'constituency_id',
                DB::raw('COUNT(DISTINCT polling_station_id) as reporting_stations'),
                DB::raw('COUNT(id) as submissions'),
                DB::raw('SUM(obtained_votes) as total_valid_votes'),
                DB::raw('SUM(total_rejected_ballot) as total_rejected_ballots'),
                DB::raw('SUM(total_ballot) as total_ballots'),
                DB::raw('MAX(updated_at) as updated_at')
            )
            ->groupBy('constituency_id')
            ->get()
            ->keyBy('constituency_id');

        $stations = PollingStation::query()
            ->select(
                'constituency_id',
                DB::raw('COUNT(id) as total_polling_stations'),
                DB::raw('SUM(total_voters) as total_voters')
            )
            ->groupBy('constituency_id')
            ->get()
            ->keyBy('constituency_id');

        $leaders = $this->buildLeadingPartyMap('constituency_id', $electionTypeId, $startupId);

        return Constituency::query()
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'region_id'])
            ->map(function ($constituency) use ($results, $stations, $leaders) {
                $resultRow = $results->get($constituency->id);
                $stationRow = $stations->get($constituency->id);
                $leader = $leaders->get($constituency->id, [
                    'party_initial' => 'N/A',
                    'party_name' => 'No verified results',
                    'votes' => 0,
                ]);

                $reportingStations = (int) optional($resultRow)->reporting_stations;
                $totalStations = (int) optional($stationRow)->total_polling_stations;
                $totalValidVotes = (int) optional($resultRow)->total_valid_votes;
                $totalRejectedBallots = (int) optional($resultRow)->total_rejected_ballots;
                $totalBallots = (int) optional($resultRow)->total_ballots;

                return [
                    'id' => (int) $constituency->id,
                    'constituency_id' => (int) $constituency->id,
                    'constituency_name' => $constituency->name,
                    'reporting_stations' => $reportingStations,
                    'submissions' => (int) optional($resultRow)->submissions,
                    'total_polling_stations' => $totalStations,
                    'coverage_percentage' => $totalStations > 0 ? round(($reportingStations / $totalStations) * 100, 2) : 0,
                    'total_valid_votes' => $totalValidVotes,
                    'total_rejected_ballots' => $totalRejectedBallots,
                    'total_ballots' => $totalBallots,
                    'rejected_rate' => $totalBallots > 0 ? round(($totalRejectedBallots / $totalBallots) * 100, 2) : 0,
                    'leading_party' => $leader['party_initial'],
                    'leading_party_name' => $leader['party_name'],
                    'leading_votes' => (int) $leader['votes'],
                    'total_voters' => (int) optional($stationRow)->total_voters,
                    'updated_at' => $this->formatDateTime(optional($resultRow)->updated_at),
                ];
            })
            ->values();
    }

    public function getRegionDetail(int $regionId, ?int $startupId = null): array
    {
        $electionTypeId = $this->dashboardService->getPresidentialElectionTypeId();
        $startupContext = $this->getStartupContext($electionTypeId, $startupId);
        $region = Region::findOrFail($regionId);
        $rows = $this->getRegionalRows($electionTypeId, $startupContext['selected_id']);
        $summary = $rows->firstWhere('region_id', $regionId) ?: [
            'region_name' => $region->name,
            'reporting_stations' => 0,
            'submissions' => 0,
            'total_polling_stations' => 0,
            'coverage_percentage' => 0,
            'total_valid_votes' => 0,
            'total_rejected_ballots' => 0,
            'total_ballots' => 0,
            'rejected_rate' => 0,
            'leading_party' => 'N/A',
            'leading_party_name' => 'No verified results',
            'leading_votes' => 0,
            'updated_at' => 'Not available',
        ];

        $partyBreakdown = $this->buildPartyBreakdown($electionTypeId, $startupContext['selected_id'], ['region_id' => $regionId]);
        $subordinateRows = $this->getRegionConstituencyBreakdown($regionId, $electionTypeId, $startupContext['selected_id']);

        return [
            'regionalDetail' => $region,
            'startupOptions' => $startupContext['options'],
            'selectedStartupId' => $startupContext['selected_id'],
            'selectedStartupName' => $startupContext['selected_name'],
            'summary' => $summary,
            'partyBreakdown' => $partyBreakdown,
            'subordinateRows' => $subordinateRows,
        ];
    }

    public function getConstituencyDetail(int $constituencyId, ?int $startupId = null): array
    {
        $electionTypeId = $this->dashboardService->getConstituencyElectionTypeId();
        $startupContext = $this->getStartupContext($electionTypeId, $startupId);
        $constituency = Constituency::findOrFail($constituencyId);
        $rows = $this->getConstituencyRows($electionTypeId, $startupContext['selected_id']);
        $summary = $rows->firstWhere('constituency_id', $constituencyId) ?: [
            'constituency_name' => $constituency->name,
            'reporting_stations' => 0,
            'submissions' => 0,
            'total_polling_stations' => 0,
            'coverage_percentage' => 0,
            'total_valid_votes' => 0,
            'total_rejected_ballots' => 0,
            'total_ballots' => 0,
            'rejected_rate' => 0,
            'leading_party' => 'N/A',
            'leading_party_name' => 'No verified results',
            'leading_votes' => 0,
            'updated_at' => 'Not available',
        ];

        $partyBreakdown = $this->buildPartyBreakdown($electionTypeId, $startupContext['selected_id'], ['constituency_id' => $constituencyId]);
        $subordinateRows = $this->getConstituencyPollingBreakdown($constituencyId, $electionTypeId, $startupContext['selected_id']);

        return [
            'constituency_detail' => $constituency,
            'startupOptions' => $startupContext['options'],
            'selectedStartupId' => $startupContext['selected_id'],
            'selectedStartupName' => $startupContext['selected_name'],
            'summary' => $summary,
            'partyBreakdown' => $partyBreakdown,
            'subordinateRows' => $subordinateRows,
        ];
    }

    public function getFilteredBreakdown(array $filters): array
    {
        $electionTypeId = isset($filters['election_type_id']) && $filters['election_type_id'] !== 'all'
            ? (int) $filters['election_type_id']
            : null;

        if (!$electionTypeId) {
            return [
                ['label' => 'No Results', 'y' => 0.0],
            ];
        }

        $startupFilter = $filters['election_start_up_id'] ?? null;
        $startupId = $startupFilter !== null && $startupFilter !== '' && $startupFilter !== 'all'
            ? (int) $startupFilter
            : null;

        if ($electionTypeId && ($startupFilter === null || $startupFilter === '')) {
            $startupId = $this->getStartupContext($electionTypeId, null)['selected_id'];
        }

        $scopeFilters = [];
        foreach (['region_id', 'constituency_id', 'electoral_area_id', 'polling_station_id'] as $field) {
            if (!empty($filters[$field]) && $filters[$field] !== 'all') {
                $scopeFilters[$field] = (int) $filters[$field];
            }
        }

        return $this->buildPartyBreakdown($electionTypeId, $startupId, $scopeFilters);
    }

    protected function buildNationalSummary(?int $electionTypeId, ?int $startupId): array
    {
        $baseQuery = $this->baseElectionResultQuery($electionTypeId, $startupId);
        $summary = (clone $baseQuery)
            ->selectRaw('
                COUNT(id) as submissions,
                COUNT(DISTINCT polling_station_id) as reporting_stations,
                COUNT(DISTINCT region_id) as reporting_regions,
                COUNT(DISTINCT constituency_id) as reporting_constituencies,
                SUM(obtained_votes) as total_valid_votes,
                SUM(total_rejected_ballot) as total_rejected_ballots,
                SUM(total_ballot) as total_ballots,
                MAX(updated_at) as updated_at
            ')
            ->first();

        $totalPollingStations = (int) PollingStation::count();
        $reportingStations = (int) ($summary->reporting_stations ?? 0);
        $totalBallots = (int) ($summary->total_ballots ?? 0);
        $rejectedBallots = (int) ($summary->total_rejected_ballots ?? 0);
        $partyBreakdown = $this->buildPartyBreakdown($electionTypeId, $startupId);
        $leadingParty = collect($partyBreakdown)->sortByDesc('votes')->first();

        return [
            'submissions' => (int) ($summary->submissions ?? 0),
            'reporting_stations' => $reportingStations,
            'total_polling_stations' => $totalPollingStations,
            'coverage_percentage' => $totalPollingStations > 0 ? round(($reportingStations / $totalPollingStations) * 100, 2) : 0,
            'reporting_regions' => (int) ($summary->reporting_regions ?? 0),
            'reporting_constituencies' => (int) ($summary->reporting_constituencies ?? 0),
            'total_valid_votes' => (int) ($summary->total_valid_votes ?? 0),
            'total_rejected_ballots' => $rejectedBallots,
            'total_ballots' => $totalBallots,
            'rejected_rate' => $totalBallots > 0 ? round(($rejectedBallots / $totalBallots) * 100, 2) : 0,
            'leading_party' => $leadingParty['party_initial'] ?? 'N/A',
            'leading_party_name' => $leadingParty['party_name'] ?? 'No verified results',
            'leading_party_votes' => (int) ($leadingParty['votes'] ?? 0),
            'updated_at' => $this->formatDateTime($summary->updated_at ?? null),
        ];
    }

    protected function buildParliamentarySummary(?int $electionTypeId, ?int $startupId): array
    {
        $summary = $this->buildNationalSummary($electionTypeId, $startupId);
        $totalConstituencies = (int) Constituency::count();
        $leadershipBreakdown = collect($this->buildLeadershipBreakdown('constituency_id', $electionTypeId, $startupId));
        $decidedRows = $leadershipBreakdown->reject(function ($row) {
            return ($row['is_tie'] ?? false) === true;
        });
        $leadingParty = $decidedRows->sortByDesc('count')->first();
        $tiedCount = (int) $leadershipBreakdown
            ->filter(function ($row) {
                return ($row['is_tie'] ?? false) === true;
            })
            ->sum('count');

        $reportedConstituencies = (int) ($summary['reporting_constituencies'] ?? 0);

        return array_merge($summary, [
            'total_constituencies' => $totalConstituencies,
            'remaining_constituencies' => max($totalConstituencies - $reportedConstituencies, 0),
            'constituency_coverage_percentage' => $totalConstituencies > 0
                ? round(($reportedConstituencies / $totalConstituencies) * 100, 2)
                : 0,
            'decided_constituencies' => (int) $decidedRows->sum('count'),
            'tied_constituencies' => $tiedCount,
            'leading_party' => $leadingParty['party_initial'] ?? ($tiedCount > 0 ? 'TIE' : 'N/A'),
            'leading_party_name' => $leadingParty['party_name'] ?? ($tiedCount > 0 ? 'No clear parliamentary leader yet' : 'No verified results'),
            'leading_party_votes' => (int) ($leadingParty['count'] ?? 0),
        ]);
    }

    protected function buildPartyBreakdown(?int $electionTypeId, ?int $startupId, array $filters = []): array
    {
        $query = DB::table('party_election_result')
            ->join('election_result', 'election_result.id', '=', 'party_election_result.election_result_id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->select(
                'political_party.id',
                'political_party.name as party_name',
                'political_party.party_initial',
                DB::raw('SUM(party_election_result.obtained_vote) as votes')
            )
            ->where('election_result.verify_by_constituency', 1)
            ->groupBy('political_party.id', 'political_party.name', 'political_party.party_initial')
            ->orderByDesc('votes');

        if ($electionTypeId) {
            $query->where('election_result.election_type_id', $electionTypeId);
        }

        if ($startupId) {
            $query->where('election_result.election_start_up_id', $startupId);
        }

        foreach ($filters as $field => $value) {
            if ($value) {
                $query->where('election_result.'.$field, $value);
            }
        }

        $rows = $query->get();
        $totalVotes = (int) $rows->sum('votes');

        if ($totalVotes <= 0 || $rows->isEmpty()) {
            return [
                [
                    'party_initial' => 'N/A',
                    'party_name' => 'No verified results',
                    'votes' => 0,
                    'percentage' => 0,
                    'label' => 'No Results',
                    'y' => 0.0,
                ],
            ];
        }

        return $rows->map(function ($row) use ($totalVotes) {
            $votes = (int) $row->votes;
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 2) : 0;

            return [
                'party_initial' => $row->party_initial,
                'party_name' => $row->party_name,
                'votes' => $votes,
                'percentage' => $percentage,
                'label' => $row->party_initial.' - '.number_format($votes),
                'y' => $percentage,
            ];
        })->values()->all();
    }

    protected function buildLeadershipBreakdown(string $groupField, ?int $electionTypeId, ?int $startupId): array
    {
        $leaders = $this->buildLeadingPartyMap($groupField, $electionTypeId, $startupId);

        if ($leaders->isEmpty()) {
            return [
                [
                    'party_initial' => 'N/A',
                    'party_name' => 'No verified results',
                    'count' => 0,
                    'percentage' => 0,
                    'label' => 'No Results',
                    'is_tie' => false,
                ],
            ];
        }

        $totalGroups = (int) $leaders->count();

        return $leaders
            ->groupBy(function ($row) {
                return $row['party_initial'].'|'.$row['party_name'].'|'.(($row['is_tie'] ?? false) ? '1' : '0');
            })
            ->map(function ($group) use ($totalGroups) {
                $sample = $group->first();
                $count = $group->count();

                return [
                    'party_initial' => $sample['party_initial'],
                    'party_name' => $sample['party_name'],
                    'count' => $count,
                    'percentage' => $totalGroups > 0 ? round(($count / $totalGroups) * 100, 2) : 0,
                    'label' => $sample['party_initial'].' - '.number_format($count),
                    'is_tie' => (bool) ($sample['is_tie'] ?? false),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    protected function getRegionConstituencyBreakdown(int $regionId, ?int $electionTypeId, ?int $startupId): Collection
    {
        $results = $this->baseElectionResultQuery($electionTypeId, $startupId)
            ->where('region_id', $regionId)
            ->select(
                'constituency_id',
                DB::raw('COUNT(DISTINCT polling_station_id) as reporting_stations'),
                DB::raw('SUM(obtained_votes) as total_valid_votes'),
                DB::raw('SUM(total_rejected_ballot) as total_rejected_ballots'),
                DB::raw('SUM(total_ballot) as total_ballots')
            )
            ->groupBy('constituency_id')
            ->get()
            ->keyBy('constituency_id');

        $stations = PollingStation::query()
            ->where('region_id', $regionId)
            ->select(
                'constituency_id',
                DB::raw('COUNT(id) as total_polling_stations')
            )
            ->groupBy('constituency_id')
            ->get()
            ->keyBy('constituency_id');

        return Constituency::query()
            ->where('region_id', $regionId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name'])
            ->map(function ($constituency) use ($results, $stations) {
                $resultRow = $results->get($constituency->id);
                $stationRow = $stations->get($constituency->id);
                $reportingStations = (int) optional($resultRow)->reporting_stations;
                $totalStations = (int) optional($stationRow)->total_polling_stations;

                return [
                    'name' => $constituency->name,
                    'reporting_stations' => $reportingStations,
                    'total_polling_stations' => $totalStations,
                    'coverage_percentage' => $totalStations > 0 ? round(($reportingStations / $totalStations) * 100, 2) : 0,
                    'total_valid_votes' => (int) optional($resultRow)->total_valid_votes,
                    'total_rejected_ballots' => (int) optional($resultRow)->total_rejected_ballots,
                    'total_ballots' => (int) optional($resultRow)->total_ballots,
                ];
            })
            ->values();
    }

    protected function getConstituencyPollingBreakdown(int $constituencyId, ?int $electionTypeId, ?int $startupId): Collection
    {
        $results = $this->baseElectionResultQuery($electionTypeId, $startupId)
            ->where('constituency_id', $constituencyId)
            ->select(
                'polling_station_id',
                DB::raw('COUNT(id) as submissions'),
                DB::raw('SUM(obtained_votes) as total_valid_votes'),
                DB::raw('SUM(total_rejected_ballot) as total_rejected_ballots'),
                DB::raw('SUM(total_ballot) as total_ballots')
            )
            ->groupBy('polling_station_id')
            ->get()
            ->keyBy('polling_station_id');

        return PollingStation::query()
            ->where('constituency_id', $constituencyId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'polling_station_id', 'total_voters'])
            ->map(function ($station) use ($results) {
                $resultRow = $results->get($station->id);
                return [
                    'polling_station_name' => $station->name,
                    'polling_station_code' => $station->polling_station_id,
                    'total_voters' => (int) $station->total_voters,
                    'submitted' => (int) optional($resultRow)->submissions,
                    'total_valid_votes' => (int) optional($resultRow)->total_valid_votes,
                    'total_rejected_ballots' => (int) optional($resultRow)->total_rejected_ballots,
                    'total_ballots' => (int) optional($resultRow)->total_ballots,
                ];
            })
            ->values();
    }

    protected function buildLeadingPartyMap(string $groupField, ?int $electionTypeId, ?int $startupId): Collection
    {
        $rows = DB::table('party_election_result')
            ->join('election_result', 'election_result.id', '=', 'party_election_result.election_result_id')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->select(
                'election_result.'.$groupField.' as group_id',
                'political_party.party_initial',
                'political_party.name as party_name',
                DB::raw('SUM(party_election_result.obtained_vote) as votes')
            )
            ->where('election_result.verify_by_constituency', 1)
            ->when($electionTypeId, function ($query, $resolvedElectionTypeId) {
                return $query->where('election_result.election_type_id', $resolvedElectionTypeId);
            })
            ->when($startupId, function ($query, $resolvedStartupId) {
                return $query->where('election_result.election_start_up_id', $resolvedStartupId);
            })
            ->groupBy('election_result.'.$groupField, 'political_party.party_initial', 'political_party.name')
            ->orderByDesc('votes')
            ->get();

        return $rows
            ->groupBy('group_id')
            ->map(function ($group) {
                $sorted = $group->sortByDesc('votes')->values();
                $leader = $sorted->first();
                $runnerUp = $sorted->get(1);
                $isTie = $leader && $runnerUp && (int) $leader->votes === (int) $runnerUp->votes;

                return [
                    'party_initial' => $isTie ? 'TIE' : $leader->party_initial,
                    'party_name' => $isTie ? 'Tie between top parties' : $leader->party_name,
                    'votes' => $isTie ? (int) $leader->votes : (int) $leader->votes,
                    'is_tie' => $isTie,
                ];
            });
    }

    protected function baseElectionResultQuery(?int $electionTypeId, ?int $startupId)
    {
        return DB::table('election_result')
            ->where('verify_by_constituency', 1)
            ->when($electionTypeId, function ($query, $resolvedElectionTypeId) {
                return $query->where('election_type_id', $resolvedElectionTypeId);
            })
            ->when($startupId, function ($query, $resolvedStartupId) {
                return $query->where('election_start_up_id', $resolvedStartupId);
            });
    }

    protected function getStartupContext(?int $electionTypeId, ?int $requestedStartupId = null): array
    {
        $options = ElectionStartupDetail::query()
            ->when($electionTypeId, function ($query, $resolvedElectionTypeId) {
                return $query->where('election_type_id', $resolvedElectionTypeId);
            })
            ->orderByDesc('status')
            ->orderByDesc('start')
            ->orderByDesc('id')
            ->get(['id', 'election_name', 'status', 'start', 'end']);

        $selected = $options->firstWhere('id', $requestedStartupId);
        if (!$selected) {
            $selected = $options->firstWhere('status', 1) ?: $options->first();
        }

        return [
            'options' => $options,
            'selected_id' => $selected?->id,
            'selected_name' => $selected?->election_name ?? 'All verified results',
        ];
    }

    protected function formatDateTime($dateTime): string
    {
        if (!$dateTime) {
            return 'Not available';
        }

        return Carbon::parse($dateTime)->format('M d, Y g:i A');
    }

    protected function buildCoverageWarning(int $reported, int $total, string $context): ?string
    {
        if ($total <= 0) {
            return null;
        }

        $percentage = round(($reported / $total) * 100, 2);

        if ($reported === 0) {
            return 'No verified '.$context.' results have been reported yet, so this page is only showing placeholders.';
        }

        if ($percentage < 25) {
            return 'Only '.number_format($reported).' of '.number_format($total).' reporting units are reflected in this '.$context.' view so far. Treat the charts as an early partial picture, not a final national trend.';
        }

        return null;
    }
}
