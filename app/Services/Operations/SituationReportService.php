<?php

namespace App\Services\Operations;

use App\Model\SituationReport;
use App\User;
use Illuminate\Support\Facades\DB;

class SituationReportService
{
    public function generate(User $viewer): SituationReport
    {
        [$scopeType, $scopeId] = $this->scope($viewer);
        $snapshot = $this->buildSnapshot($viewer);

        return SituationReport::create([
            'scope_type' => $scopeType, 'scope_id' => $scopeId, 'generated_by' => $viewer->id,
            'period_ending' => now(), 'snapshot' => $snapshot,
            'checksum' => hash('sha256', json_encode($snapshot, JSON_UNESCAPED_SLASHES)),
        ]);
    }

    public function generateIfDue(User $viewer): ?SituationReport
    {
        [$scopeType, $scopeId] = $this->scope($viewer);
        $alreadyGenerated = SituationReport::where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->where('created_at', '>=', now()->startOfHour())
            ->exists();

        return $alreadyGenerated ? null : $this->generate($viewer);
    }

    public function buildSnapshot(User $viewer): array
    {
        [$scopeType, $scopeId] = $this->scope($viewer);
        $results = DB::table('election_result')
            ->when($scopeType === 'region', fn ($q) => $q->where('region_id', $scopeId))
            ->when($scopeType === 'constituency', fn ($q) => $q->where('constituency_id', $scopeId));

        $summary = (clone $results)->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN approval_status = "approved" THEN 1 ELSE 0 END) AS approved')
            ->selectRaw('SUM(CASE WHEN approval_status = "pending" THEN 1 ELSE 0 END) AS pending')
            ->selectRaw('SUM(CASE WHEN approval_status = "rejected" THEN 1 ELSE 0 END) AS rejected')
            ->selectRaw('SUM(CASE WHEN approval_status = "pending" AND updated_at < DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 1 ELSE 0 END) AS stale_pending')
            ->first();
        $approvedVotes = (clone $results)->where('approval_status', 'approved')->sum('obtained_votes');
        $incidents = DB::table('result_escalations')->join('election_result', 'election_result.id', '=', 'result_escalations.election_result_id')
            ->when($scopeType === 'region', fn ($q) => $q->where('election_result.region_id', $scopeId))
            ->when($scopeType === 'constituency', fn ($q) => $q->where('election_result.constituency_id', $scopeId));
        $incidentSummary = (clone $incidents)->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN result_escalations.status != "resolved" THEN 1 ELSE 0 END) AS active_count')
            ->selectRaw('SUM(CASE WHEN result_escalations.status != "resolved" AND result_escalations.severity IN ("critical", "high") THEN 1 ELSE 0 END) AS high_priority_count')->first();

        return [
            'scope' => ['type' => $scopeType, 'id' => $scopeId],
            'generated_at' => now()->toIso8601String(),
            'results' => [
                'total' => (int) ($summary->total ?? 0), 'approved' => (int) ($summary->approved ?? 0),
                'pending' => (int) ($summary->pending ?? 0), 'rejected' => (int) ($summary->rejected ?? 0),
                'stale_pending' => (int) ($summary->stale_pending ?? 0), 'approved_valid_votes' => (int) $approvedVotes,
            ],
            'incidents' => [
                'total' => (int) ($incidentSummary->total ?? 0), 'active' => (int) ($incidentSummary->active_count ?? 0),
                'high_priority' => (int) ($incidentSummary->high_priority_count ?? 0),
            ],
        ];
    }

    public function visibleReports(User $viewer)
    {
        [$scopeType, $scopeId] = $this->scope($viewer);
        return SituationReport::where('scope_type', $scopeType)->where('scope_id', $scopeId)->latest('id')->paginate(20);
    }

    public function visibleReport(User $viewer, int $id): SituationReport
    {
        [$scopeType, $scopeId] = $this->scope($viewer);
        return SituationReport::where('id', $id)->where('scope_type', $scopeType)->where('scope_id', $scopeId)->firstOrFail();
    }

    private function scope(User $viewer): array
    {
        return match ((int) $viewer->user_type_id) {
            User::TYPE_NATIONAL_DIRECTOR => ['national', null],
            User::TYPE_REGIONAL_DIRECTOR => ['region', (int) $viewer->region_id],
            default => ['constituency', (int) $viewer->constituency_id],
        };
    }
}
