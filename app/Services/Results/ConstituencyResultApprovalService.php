<?php

namespace App\Services\Results;

use App\Model\ElectionResult;
use App\Model\PartyElectionResult;
use App\Model\PollingStation;
use App\Model\ResultApprovalAudit;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConstituencyResultApprovalService
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function decide(
        int $resultId,
        User $director,
        string $action,
        ?string $reason,
        ?string $ipAddress,
        ?string $userAgent
    ): ElectionResult {
        return DB::transaction(function () use ($resultId, $director, $action, $reason, $ipAddress, $userAgent) {
            $result = ElectionResult::query()->lockForUpdate()->findOrFail($resultId);

            if ((int) $result->constituency_id !== (int) $director->constituency_id) {
                abort(403, 'You may only review results from your assigned constituency.');
            }

            $fromStatus = $this->currentStatus($result);
            $normalizedReason = trim((string) $reason);

            if ($action === 'approve') {
                $this->approve($result, $director, $fromStatus);
            } elseif ($action === 'reject') {
                $this->reject($result, $director, $fromStatus, $normalizedReason);
            } elseif ($action === 'reopen') {
                $this->reopen($result, $fromStatus, $normalizedReason);
            } else {
                throw ValidationException::withMessages(['action' => 'Unknown result decision.']);
            }

            $result->save();
            $this->recordAudit($result, $director, $action, $fromStatus, $normalizedReason, $ipAddress, $userAgent);

            return $result->fresh();
        });
    }

    public function integrityIssues(ElectionResult $result): array
    {
        $issues = [];
        $partyRows = PartyElectionResult::where('election_result_id', $result->id)->get();
        $partyVoteTotal = (int) $partyRows->sum('obtained_vote');
        $validVotes = (int) $result->obtained_votes;
        $rejectedVotes = (int) $result->total_rejected_ballot;
        $totalBallots = (int) $result->total_ballot;
        $registeredVoters = (int) PollingStation::where('id', $result->polling_station_id)->value('total_voters');

        if ($partyRows->isEmpty()) {
            $issues[] = 'No candidate or party vote entries were submitted.';
        }
        if ($partyRows->contains(fn ($row) => (int) $row->obtained_vote < 0) || $rejectedVotes < 0) {
            $issues[] = 'Vote totals cannot contain negative values.';
        }
        if ($partyVoteTotal !== $validVotes) {
            $issues[] = 'Candidate votes do not equal the recorded valid-vote total.';
        }
        if (($validVotes + $rejectedVotes) !== $totalBallots) {
            $issues[] = 'Valid votes plus rejected ballots do not equal total ballots.';
        }
        if ($registeredVoters > 0 && $totalBallots > $registeredVoters) {
            $issues[] = 'Total ballots exceed the registered voters at this polling station.';
        }
        if (empty($result->pink_sheet_path)) {
            $issues[] = 'A pink sheet must be uploaded before approval.';
        } elseif (!$this->pinkSheetExists($result->pink_sheet_path)) {
            $issues[] = 'The recorded pink sheet file could not be found. Upload it again before approval.';
        }

        return $issues;
    }

    private function approve(ElectionResult $result, User $director, string $fromStatus): void
    {
        if ($fromStatus === self::STATUS_APPROVED) {
            throw ValidationException::withMessages(['result' => 'This result is already approved.']);
        }

        $issues = $this->integrityIssues($result);
        if ($issues) {
            throw ValidationException::withMessages(['result' => $issues]);
        }

        $result->approval_status = self::STATUS_APPROVED;
        $result->verify_by_constituency = 1;
        $result->result_by_constituency = $director->id;
        $result->approved_by = $director->id;
        $result->approved_at = now();
        $result->rejected_by = null;
        $result->rejected_at = null;
        $result->rejection_reason = null;
    }

    private function reject(ElectionResult $result, User $director, string $fromStatus, string $reason): void
    {
        if ($fromStatus === self::STATUS_APPROVED) {
            throw ValidationException::withMessages(['result' => 'Reopen the approved result before rejecting it.']);
        }
        if ($fromStatus === self::STATUS_REJECTED) {
            throw ValidationException::withMessages(['result' => 'This result is already rejected and awaiting correction.']);
        }
        $this->validateReason($reason, 'A rejection reason is required.');

        $result->approval_status = self::STATUS_REJECTED;
        $result->verify_by_constituency = 0;
        $result->result_by_constituency = null;
        $result->rejected_by = $director->id;
        $result->rejected_at = now();
        $result->rejection_reason = $reason;
    }

    private function reopen(ElectionResult $result, string $fromStatus, string $reason): void
    {
        if ($fromStatus !== self::STATUS_APPROVED) {
            throw ValidationException::withMessages(['result' => 'Only an approved result can be reopened.']);
        }
        $this->validateReason($reason, 'A reason is required to reopen an approved result.');

        $result->approval_status = self::STATUS_PENDING;
        $result->verify_by_constituency = 0;
        $result->result_by_constituency = null;
        $result->approved_by = null;
        $result->approved_at = null;
    }

    private function validateReason(string $reason, string $requiredMessage): void
    {
        if (mb_strlen($reason) < 10) {
            throw ValidationException::withMessages(['reason' => $requiredMessage.' Please provide at least 10 characters.']);
        }
        if (mb_strlen($reason) > 1000) {
            throw ValidationException::withMessages(['reason' => 'The review reason may not exceed 1,000 characters.']);
        }
    }

    private function pinkSheetExists(string $filename): bool
    {
        return is_file(storage_path('app/pink_sheets').DIRECTORY_SEPARATOR.$filename)
            || is_file(public_path('pink_sheets').DIRECTORY_SEPARATOR.$filename);
    }

    private function currentStatus(ElectionResult $result): string
    {
        if (in_array($result->approval_status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED], true)) {
            return $result->approval_status;
        }

        return (int) $result->verify_by_constituency === 1 ? self::STATUS_APPROVED : self::STATUS_PENDING;
    }

    private function recordAudit(
        ElectionResult $result,
        User $director,
        string $action,
        string $fromStatus,
        string $reason,
        ?string $ipAddress,
        ?string $userAgent
    ): void {
        $partyVotes = PartyElectionResult::where('election_result_id', $result->id)
            ->orderBy('candidate_id')
            ->get(['candidate_id', 'party_id', 'obtained_vote'])
            ->map(fn ($row) => [
                'candidate_id' => (int) $row->candidate_id,
                'party_id' => (int) $row->party_id,
                'votes' => (int) $row->obtained_vote,
            ])->values()->all();

        ResultApprovalAudit::create([
            'election_result_id' => $result->id,
            'actor_id' => $director->id,
            'actor_role' => 'Constituency Director',
            'constituency_id' => $result->constituency_id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $result->approval_status,
            'reason' => $reason ?: null,
            'result_snapshot' => [
                'polling_station_id' => (int) $result->polling_station_id,
                'election_start_up_id' => (int) $result->election_start_up_id,
                'valid_votes' => (int) $result->obtained_votes,
                'rejected_ballots' => (int) $result->total_rejected_ballot,
                'total_ballots' => (int) $result->total_ballot,
                'pink_sheet_path' => $result->pink_sheet_path,
                'party_votes' => $partyVotes,
            ],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 1000) : null,
        ]);
    }
}
