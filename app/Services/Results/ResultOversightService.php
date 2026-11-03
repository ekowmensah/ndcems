<?php

namespace App\Services\Results;

use App\Model\ElectionResult;
use App\Model\ElectionStartupDetail;
use App\Model\ElectionType;
use App\Model\PartyElectionResult;
use App\Model\Region;
use App\Model\ResultApprovalAudit;
use App\Model\ResultEscalation;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ResultOversightService
{
    public function getPageData(array $filters, ?int $regionId = null): array
    {
        $base = $this->baseQuery($regionId, $filters);
        $summary = (clone $base)->selectRaw('COUNT(DISTINCT election_result.id) as total')
            ->selectRaw('COUNT(DISTINCT CASE WHEN election_result.approval_status = "approved" THEN election_result.id END) as approved')
            ->selectRaw('COUNT(DISTINCT CASE WHEN election_result.approval_status = "pending" THEN election_result.id END) as pending')
            ->selectRaw('COUNT(DISTINCT CASE WHEN election_result.approval_status = "rejected" THEN election_result.id END) as rejected')
            ->selectRaw('COUNT(DISTINCT CASE WHEN election_result.pink_sheet_path IS NULL OR election_result.pink_sheet_path = "" THEN election_result.id END) as missing_pink_sheets')
            ->selectRaw('COUNT(DISTINCT CASE WHEN CAST(election_result.total_ballot AS UNSIGNED) > CAST(pollingstation.total_voters AS UNSIGNED) THEN election_result.id END) as over_voting')
            ->selectRaw('COUNT(DISTINCT CASE WHEN election_result.total_ballot != (election_result.obtained_votes + election_result.total_rejected_ballot) THEN election_result.id END) as arithmetic_mismatch')
            ->selectRaw('COUNT(DISTINCT CASE WHEN COALESCE(party_totals.party_votes, 0) != election_result.obtained_votes THEN election_result.id END) as party_mismatch')
            ->selectRaw('COUNT(DISTINCT CASE WHEN election_result.approval_status = "pending" AND election_result.updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN election_result.id END) as stale_pending')
            ->selectRaw('COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM result_approval_audits WHERE result_approval_audits.election_result_id = election_result.id AND result_approval_audits.action = "reopen") THEN election_result.id END) as reopened')
            ->selectRaw('COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM result_escalations WHERE result_escalations.election_result_id = election_result.id AND result_escalations.status = "open") THEN election_result.id END) as escalated')
            ->first();

        $rowsQuery = $this->baseQuery($regionId, $filters)
            ->select([
                'election_result.id',
                'election_result.approval_status',
                'election_result.obtained_votes',
                'election_result.total_rejected_ballot',
                'election_result.total_ballot',
                'election_result.pink_sheet_path',
                'election_result.rejection_reason',
                'election_result.approved_at',
                'election_result.updated_at',
                'pollingstation.name as polling_station_name',
                'pollingstation.polling_station_id as polling_station_code',
                'pollingstation.total_voters',
                'constituency.name as constituency_name',
                'region.name as region_name',
                'election_type.name as election_type_name',
                'election_startup_detail.election_name',
                'reviewer.name as reviewer_name',
                DB::raw('COALESCE(party_totals.party_votes, 0) as party_votes'),
                DB::raw('(SELECT COUNT(*) FROM result_escalations WHERE result_escalations.election_result_id = election_result.id AND result_escalations.status = "open") as open_escalations'),
                DB::raw('(SELECT COUNT(*) FROM result_approval_audits WHERE result_approval_audits.election_result_id = election_result.id AND result_approval_audits.action = "reopen") as reopening_count'),
            ])
            ->orderByRaw('CASE election_result.approval_status WHEN "pending" THEN 1 WHEN "rejected" THEN 2 ELSE 3 END')
            ->orderByDesc('election_result.updated_at');

        $status = $filters['status'] ?? null;
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $rowsQuery->where('election_result.approval_status', $status);
        }
        if (($filters['anomaly'] ?? null) === 'over_voting') {
            $rowsQuery->whereRaw('CAST(election_result.total_ballot AS UNSIGNED) > CAST(pollingstation.total_voters AS UNSIGNED)');
        } elseif (($filters['anomaly'] ?? null) === 'arithmetic') {
            $rowsQuery->whereRaw('election_result.total_ballot != (election_result.obtained_votes + election_result.total_rejected_ballot)');
        } elseif (($filters['anomaly'] ?? null) === 'party_mismatch') {
            $rowsQuery->whereRaw('COALESCE(party_totals.party_votes, 0) != election_result.obtained_votes');
        } elseif (($filters['anomaly'] ?? null) === 'missing_pink_sheet') {
            $rowsQuery->where(function ($query) {
                $query->whereNull('election_result.pink_sheet_path')->orWhere('election_result.pink_sheet_path', '');
            });
        } elseif (($filters['anomaly'] ?? null) === 'escalated') {
            $rowsQuery->whereExists(function ($query) {
                $query->selectRaw('1')->from('result_escalations')
                    ->whereColumn('result_escalations.election_result_id', 'election_result.id')
                    ->where('result_escalations.status', 'open');
            });
        } elseif (($filters['anomaly'] ?? null) === 'stale_pending') {
            $rowsQuery->where('election_result.approval_status', 'pending')
                ->where('election_result.updated_at', '<', now()->subHours(24));
        } elseif (($filters['anomaly'] ?? null) === 'reopened') {
            $rowsQuery->whereExists(function ($query) {
                $query->selectRaw('1')->from('result_approval_audits')
                    ->whereColumn('result_approval_audits.election_result_id', 'election_result.id')
                    ->where('result_approval_audits.action', 'reopen');
            });
        }

        $rows = $rowsQuery->paginate(25)->withQueryString();
        $total = (int) ($summary->total ?? 0);
        $approved = (int) ($summary->approved ?? 0);

        return [
            'summary' => [
                'total' => $total,
                'approved' => $approved,
                'pending' => (int) ($summary->pending ?? 0),
                'rejected' => (int) ($summary->rejected ?? 0),
                'missing_pink_sheets' => (int) ($summary->missing_pink_sheets ?? 0),
                'over_voting' => (int) ($summary->over_voting ?? 0),
                'arithmetic_mismatch' => (int) ($summary->arithmetic_mismatch ?? 0),
                'party_mismatch' => (int) ($summary->party_mismatch ?? 0),
                'stale_pending' => (int) ($summary->stale_pending ?? 0),
                'reopened' => (int) ($summary->reopened ?? 0),
                'escalated' => (int) ($summary->escalated ?? 0),
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 1) : 0,
            ],
            'rows' => $rows,
            'startupOptions' => ElectionStartupDetail::orderByDesc('id')->get(['id', 'election_name']),
            'electionTypes' => ElectionType::orderBy('name')->get(['id', 'name']),
            'regions' => $regionId ? collect() : Region::orderBy('name')->get(['id', 'name']),
            'filters' => $filters,
        ];
    }

    public function getDetail(int $resultId, User $viewer, ?int $regionId = null): array
    {
        $result = $this->visibleResultQuery($resultId, $regionId)
            ->select([
                'election_result.*',
                'pollingstation.name as polling_station_name',
                'pollingstation.polling_station_id as polling_station_code',
                'pollingstation.total_voters',
                'constituency.name as constituency_name',
                'region.name as region_name',
                'election_type.name as election_type_name',
                'election_startup_detail.election_name',
                'reviewer.name as reviewer_name',
            ])->firstOrFail();

        $partyRows = PartyElectionResult::query()
            ->select('political_party.party_initial', 'candidates.first_name', 'candidates.last_name', 'party_election_result.obtained_vote')
            ->join('political_party', 'political_party.id', '=', 'party_election_result.party_id')
            ->join('candidates', 'candidates.id', '=', 'party_election_result.candidate_id')
            ->where('party_election_result.election_result_id', $resultId)
            ->orderBy('candidates.ordering_position')
            ->get();

        $history = ResultApprovalAudit::query()
            ->select('result_approval_audits.*', 'users.name as actor_name')
            ->leftJoin('users', 'users.id', '=', 'result_approval_audits.actor_id')
            ->where('election_result_id', $resultId)->orderByDesc('id')->get();

        $escalations = ResultEscalation::query()
            ->select('result_escalations.*', 'creator.name as creator_name', 'resolver.name as resolver_name')
            ->leftJoin('users as creator', 'creator.id', '=', 'result_escalations.created_by')
            ->leftJoin('users as resolver', 'resolver.id', '=', 'result_escalations.resolved_by')
            ->where('election_result_id', $resultId)->orderByDesc('id')->get();

        $pinkSheetAvailable = $this->pinkSheetExists($result->pink_sheet_path);
        $issues = $this->integrityIssues($result, (int) $partyRows->sum('obtained_vote'), $pinkSheetAvailable);

        return compact('result', 'partyRows', 'history', 'escalations', 'issues', 'pinkSheetAvailable');
    }

    public function createEscalation(int $resultId, User $viewer, array $data, string $role, ?int $regionId = null): ResultEscalation
    {
        $this->visibleResultQuery($resultId, $regionId)
            ->select('election_result.id')
            ->firstOrFail();

        return ResultEscalation::create([
            'election_result_id' => $resultId,
            'created_by' => $viewer->id,
            'created_by_role' => $role,
            'category' => $data['category'],
            'severity' => $data['severity'],
            'note' => $data['note'],
            'status' => 'open',
        ]);
    }

    public function resolveEscalation(int $escalationId, User $viewer, string $resolutionNote, ?int $regionId = null): ResultEscalation
    {
        $escalation = ResultEscalation::findOrFail($escalationId);
        $this->visibleResultQuery((int) $escalation->election_result_id, $regionId)->firstOrFail();

        if ($escalation->status !== 'open') {
            return $escalation;
        }
        $escalation->update([
            'status' => 'resolved',
            'resolved_by' => $viewer->id,
            'resolved_at' => now(),
            'resolution_note' => $resolutionNote,
        ]);

        return $escalation->fresh();
    }

    public function pinkSheetPath(int $resultId, ?int $regionId = null): string
    {
        $result = $this->visibleResultQuery($resultId, $regionId)->firstOrFail();
        abort_if(empty($result->pink_sheet_path), 404);

        foreach ([storage_path('app/pink_sheets'), public_path('pink_sheets')] as $directory) {
            $path = $directory.DIRECTORY_SEPARATOR.$result->pink_sheet_path;
            if (is_file($path)) {
                return $path;
            }
        }
        abort(404);
    }

    private function baseQuery(?int $regionId, array $filters): Builder
    {
        $partyTotals = DB::table('party_election_result')
            ->select('election_result_id', DB::raw('SUM(obtained_vote) as party_votes'))
            ->groupBy('election_result_id');

        return ElectionResult::query()
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->join('constituency', 'constituency.id', '=', 'election_result.constituency_id')
            ->join('region', 'region.id', '=', 'election_result.region_id')
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->join('election_startup_detail', 'election_startup_detail.id', '=', 'election_result.election_start_up_id')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'election_result.approved_by')
            ->leftJoinSub($partyTotals, 'party_totals', fn ($join) => $join->on('party_totals.election_result_id', '=', 'election_result.id'))
            ->when($regionId, fn ($query) => $query->where('election_result.region_id', $regionId))
            ->when(!empty($filters['region_id']) && !$regionId, fn ($query) => $query->where('election_result.region_id', (int) $filters['region_id']))
            ->when(!empty($filters['election_type_id']), fn ($query) => $query->where('election_result.election_type_id', (int) $filters['election_type_id']))
            ->when(!empty($filters['startup_id']), fn ($query) => $query->where('election_result.election_start_up_id', (int) $filters['startup_id']));
    }

    private function visibleResultQuery(int $resultId, ?int $regionId): Builder
    {
        return ElectionResult::query()
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->join('constituency', 'constituency.id', '=', 'election_result.constituency_id')
            ->join('region', 'region.id', '=', 'election_result.region_id')
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->join('election_startup_detail', 'election_startup_detail.id', '=', 'election_result.election_start_up_id')
            ->leftJoin('users as reviewer', 'reviewer.id', '=', 'election_result.approved_by')
            ->where('election_result.id', $resultId)
            ->when($regionId, fn ($query) => $query->where('election_result.region_id', $regionId));
    }

    private function integrityIssues($result, int $partyVotes, bool $pinkSheetAvailable): array
    {
        $issues = [];
        if ((int) $result->total_ballot > (int) $result->total_voters) $issues[] = 'Total ballots exceed registered voters.';
        if ((int) $result->total_ballot !== ((int) $result->obtained_votes + (int) $result->total_rejected_ballot)) $issues[] = 'Ballot arithmetic does not balance.';
        if ($partyVotes !== (int) $result->obtained_votes) $issues[] = 'Candidate vote rows do not equal the valid-vote total.';
        if (empty($result->pink_sheet_path)) {
            $issues[] = 'Pink sheet is missing.';
        } elseif (!$pinkSheetAvailable) {
            $issues[] = 'The recorded pink-sheet file could not be found.';
        }
        return $issues;
    }

    private function pinkSheetExists(?string $filename): bool
    {
        if (empty($filename)) {
            return false;
        }

        foreach ([storage_path('app/pink_sheets'), public_path('pink_sheets')] as $directory) {
            if (is_file($directory.DIRECTORY_SEPARATOR.$filename)) {
                return true;
            }
        }

        return false;
    }
}
