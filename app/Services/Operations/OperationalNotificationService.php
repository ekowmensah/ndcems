<?php

namespace App\Services\Operations;

use App\Model\OperationalNotification;
use App\User;
use Illuminate\Support\Facades\DB;

class OperationalNotificationService
{
    public function syncFor(User $user): void
    {
        $results = DB::table('election_result')
            ->join('pollingstation', 'pollingstation.id', '=', 'election_result.polling_station_id')
            ->join('constituency', 'constituency.id', '=', 'election_result.constituency_id')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('election_result.approval_status', 'pending')->where('election_result.updated_at', '<', now()->subHours(24));
                })->orWhere('election_result.approval_status', 'rejected')
                  ->orWhereRaw('CAST(election_result.total_ballot AS UNSIGNED) > CAST(pollingstation.total_voters AS UNSIGNED)')
                  ->orWhereRaw('election_result.total_ballot != (election_result.obtained_votes + election_result.total_rejected_ballot)');
            })
            ->when((int) $user->user_type_id === User::TYPE_REGIONAL_DIRECTOR, fn ($q) => $q->where('election_result.region_id', $user->region_id))
            ->when((int) $user->user_type_id === User::TYPE_CONSTITUENCY_DIRECTOR, fn ($q) => $q->where('election_result.constituency_id', $user->constituency_id))
            ->get(['election_result.id', 'election_result.approval_status', 'election_result.updated_at', 'election_result.total_ballot', 'election_result.obtained_votes', 'election_result.total_rejected_ballot', 'pollingstation.total_voters', 'pollingstation.name as station', 'constituency.name as constituency']);

        foreach ($results as $result) {
            $type = $result->approval_status === 'rejected' ? 'result_rejected' : ($result->approval_status === 'pending' ? 'review_delayed' : 'integrity_anomaly');
            $severity = $type === 'integrity_anomaly' ? 'high' : 'medium';
            $title = match ($type) {
                'result_rejected' => 'Rejected result requires correction',
                'review_delayed' => 'Constituency review is overdue',
                default => 'Result integrity exception detected',
            };
            $this->store($user, $type, $severity, $title, $result->station.' in '.$result->constituency.' requires attention.', 'election_result', (int) $result->id, $this->resultReviewUrl($user));
        }

        $incidents = DB::table('result_escalations')
            ->join('election_result', 'election_result.id', '=', 'result_escalations.election_result_id')
            ->whereIn('result_escalations.status', ['open', 'acknowledged', 'in_progress'])
            ->whereIn('result_escalations.severity', ['critical', 'high'])
            ->where(function ($q) use ($user) {
                $q->where('result_escalations.assigned_to', $user->id)
                    ->orWhere(function ($scope) use ($user) {
                        if ((int) $user->user_type_id === User::TYPE_NATIONAL_DIRECTOR) {
                            $scope->whereRaw('1 = 1');
                        } elseif ((int) $user->user_type_id === User::TYPE_REGIONAL_DIRECTOR) {
                            $scope->where('election_result.region_id', $user->region_id);
                        } else {
                            $scope->where('election_result.constituency_id', $user->constituency_id);
                        }
                    });
            })->get(['result_escalations.id', 'result_escalations.severity', 'result_escalations.category']);

        foreach ($incidents as $incident) {
            $this->store($user, 'incident_attention', $incident->severity, 'High-priority incident', 'A '.str_replace('_', ' ', $incident->category).' incident requires operational follow-up.', 'result_escalation', (int) $incident->id, route('Operations.incidents'));
        }
    }

    private function store(User $user, string $type, string $severity, string $title, string $message, string $sourceType, int $sourceId, string $actionUrl): void
    {
        OperationalNotification::firstOrCreate(
            ['fingerprint' => implode(':', [$type, $sourceType, $sourceId, $user->id])],
            [
                'recipient_user_id' => $user->id, 'type' => $type, 'severity' => $severity,
                'title' => $title, 'message' => $message, 'action_url' => $actionUrl,
                'source_type' => $sourceType, 'source_id' => $sourceId,
            ]
        );
    }

    private function resultReviewUrl(User $user): string
    {
        return match ((int) $user->user_type_id) {
            User::TYPE_NATIONAL_DIRECTOR => route('National.resultReview'),
            User::TYPE_REGIONAL_DIRECTOR => route('Region.resultReview'),
            default => route('Director.Result'),
        };
    }
}
