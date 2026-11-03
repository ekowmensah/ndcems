<?php

namespace App\Services\Operations;

use App\Model\ResultEscalation;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IncidentService
{
    public function pageData(User $viewer, array $filters): array
    {
        $query = $this->visibleQuery($viewer)
            ->select([
                'result_escalations.*', 'election_result.approval_status',
                'pollingstation.name as polling_station_name', 'pollingstation.polling_station_id as polling_station_code',
                'constituency.name as constituency_name', 'region.name as region_name',
                'election_type.name as election_type_name', 'election_startup_detail.election_name',
                'creator.name as creator_name', 'assignee.name as assignee_name',
            ])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('result_escalations.status', $status))
            ->when($filters['severity'] ?? null, fn ($q, $severity) => $q->where('result_escalations.severity', $severity))
            ->when(($filters['assignment'] ?? null) === 'mine', fn ($q) => $q->where('result_escalations.assigned_to', $viewer->id))
            ->when(($filters['assignment'] ?? null) === 'unassigned', fn ($q) => $q->whereNull('result_escalations.assigned_to'))
            ->orderByRaw('CASE result_escalations.severity WHEN "critical" THEN 1 WHEN "high" THEN 2 WHEN "medium" THEN 3 ELSE 4 END')
            ->orderByRaw('CASE result_escalations.status WHEN "open" THEN 1 WHEN "acknowledged" THEN 2 WHEN "in_progress" THEN 3 ELSE 4 END')
            ->latest('result_escalations.id');

        $summaryBase = $this->visibleQuery($viewer);
        $summary = (clone $summaryBase)->selectRaw('COUNT(DISTINCT result_escalations.id) AS total')
            ->selectRaw('SUM(CASE WHEN result_escalations.status = "open" THEN 1 ELSE 0 END) AS open_count')
            ->selectRaw('SUM(CASE WHEN result_escalations.status = "in_progress" THEN 1 ELSE 0 END) AS in_progress')
            ->selectRaw('SUM(CASE WHEN result_escalations.status = "resolved" THEN 1 ELSE 0 END) AS resolved')
            ->selectRaw('SUM(CASE WHEN result_escalations.severity IN ("critical", "high") AND result_escalations.status != "resolved" THEN 1 ELSE 0 END) AS high_priority_count')
            ->selectRaw('SUM(CASE WHEN result_escalations.due_at IS NOT NULL AND result_escalations.due_at < NOW() AND result_escalations.status != "resolved" THEN 1 ELSE 0 END) AS overdue')
            ->first();

        return [
            'incidents' => $query->paginate(20)->withQueryString(),
            'summary' => [
                'total' => (int) ($summary->total ?? 0), 'open' => (int) ($summary->open_count ?? 0),
                'in_progress' => (int) ($summary->in_progress ?? 0), 'resolved' => (int) ($summary->resolved ?? 0),
                'high_priority' => (int) ($summary->high_priority_count ?? 0), 'overdue' => (int) ($summary->overdue ?? 0),
            ],
            'assignees' => $this->assignees($viewer),
            'filters' => $filters,
        ];
    }

    public function update(User $viewer, int $incidentId, array $data): ResultEscalation
    {
        return DB::transaction(function () use ($viewer, $incidentId, $data) {
            $incident = $this->visibleQuery($viewer)->where('result_escalations.id', $incidentId)
                ->select('result_escalations.*')->lockForUpdate()->firstOrFail();

            $assignedTo = $data['assigned_to'] ?? null;
            $dueAt = $data['due_at'] ?? null;

            if ($assignedTo) {
                abort_unless($this->assignees($viewer)->contains('id', (int) $assignedTo), 422);
            }

            $changes = [
                'status' => $data['status'],
                'assigned_to' => $assignedTo ?: null,
                'due_at' => $dueAt ?: null,
                'last_activity_at' => now(),
            ];
            if (in_array($data['status'], ['acknowledged', 'in_progress'], true) && !$incident->acknowledged_at) {
                $changes['acknowledged_by'] = $viewer->id;
                $changes['acknowledged_at'] = now();
            }
            if ($data['status'] === 'resolved') {
                $changes['resolved_by'] = $viewer->id;
                $changes['resolved_at'] = now();
                $changes['resolution_note'] = $data['resolution_note'] ?? null;
            }
            $incident->update($changes);

            return $incident->fresh();
        });
    }

    private function visibleQuery(User $viewer): Builder
    {
        return ResultEscalation::query()
            ->join('election_result', 'election_result.id', '=', 'result_escalations.election_result_id')
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->join('constituency', 'constituency.id', '=', 'election_result.constituency_id')
            ->join('region', 'region.id', '=', 'election_result.region_id')
            ->join('election_type', 'election_type.id', '=', 'election_result.election_type_id')
            ->join('election_startup_detail', 'election_startup_detail.id', '=', 'election_result.election_start_up_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'result_escalations.created_by')
            ->leftJoin('users as assignee', 'assignee.id', '=', 'result_escalations.assigned_to')
            ->when((int) $viewer->user_type_id === User::TYPE_REGIONAL_DIRECTOR, fn ($q) => $q->where('election_result.region_id', $viewer->region_id))
            ->when((int) $viewer->user_type_id === User::TYPE_CONSTITUENCY_DIRECTOR, fn ($q) => $q->where('election_result.constituency_id', $viewer->constituency_id));
    }

    private function assignees(User $viewer)
    {
        return User::query()->select('id', 'name', 'user_type_id')
            ->whereIn('user_type_id', [User::TYPE_NATIONAL_DIRECTOR, User::TYPE_REGIONAL_DIRECTOR, User::TYPE_CONSTITUENCY_DIRECTOR])
            ->when((int) $viewer->user_type_id === User::TYPE_REGIONAL_DIRECTOR, fn ($q) => $q->where('region_id', $viewer->region_id))
            ->when((int) $viewer->user_type_id === User::TYPE_CONSTITUENCY_DIRECTOR, fn ($q) => $q->where('constituency_id', $viewer->constituency_id))
            ->orderBy('name')->get();
    }
}
