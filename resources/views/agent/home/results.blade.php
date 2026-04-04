@extends('layouts.app_agent')
@section('css')
<style>
    :root {
        --ndc-green: #006B3F;
        --ndc-green-dark: #004D2E;
        --ndc-red: #CE1126;
        --ndc-gold: #FCD116;
        --ndc-black: #1a1a1a;
    }
    .results-list-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .results-list-card .card-header {
        background: linear-gradient(135deg, var(--ndc-green), var(--ndc-green-dark));
        color: #fff;
        padding: 16px 20px;
        border: none;
    }
    .results-list-card .card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.05rem;
    }
    .result-item {
        padding: 14px 16px;
        border-bottom: 1px solid #f0f0f0;
        transition: background 0.2s;
    }
    .result-item:last-child { border-bottom: none; }
    .result-item:hover { background: #f8fdf9; }
    .result-item .result-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }
    .result-item .election-name {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--ndc-black);
    }
    .result-item .election-type {
        font-size: 0.78rem;
        color: #777;
        margin-top: 2px;
    }
    .result-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
    }
    .stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f4f6f7;
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.78rem;
        font-weight: 600;
        color: #444;
    }
    .stat-chip i { font-size: 0.7rem; }
    .stat-chip.over-vote {
        background: #fdeaea;
        color: var(--ndc-red);
    }
    .stat-chip.no-over-vote {
        background: #e8f5e9;
        color: var(--ndc-green);
    }
    .status-badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }
    .status-confirmed {
        background: #e8f5e9;
        color: var(--ndc-green);
    }
    .status-pending {
        background: #fff8e1;
        color: #e6a700;
    }
    .result-actions {
        display: flex;
        gap: 8px;
        margin-top: 10px;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
    }
    .btn-view {
        background: #e3f2fd;
        color: #1565c0;
    }
    .btn-view:hover { background: #bbdefb; color: #0d47a1; text-decoration: none; }
    .btn-edit {
        background: #fff3e0;
        color: #e65100;
    }
    .btn-edit:hover { background: #ffe0b2; color: #bf360c; text-decoration: none; }
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #999;
    }
    .empty-state i { font-size: 2.5rem; margin-bottom: 12px; color: #ddd; }

    @media (min-width: 768px) {
        .result-item .result-header { align-items: center; }
        .result-actions { margin-top: 0; }
        .result-row-desktop {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .result-row-desktop .left { flex: 1; }
        .result-row-desktop .right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
    }
</style>
@endsection
@section('content')

<div class="container mt-3">
    <div class="card results-list-card">
        <div class="card-header">
            <h5><i class="fas fa-poll mr-2"></i>My Submitted Results</h5>
        </div>
        <div class="card-body p-0">
            @if(count($electionResults) > 0)
                @foreach ($electionResults as $electionResult)
                <div class="result-item">
                    <div class="d-md-flex align-items-center justify-content-between">
                        <div>
                            <div class="election-name">{{ $electionResult->election_name }}</div>
                            <div class="election-type">{{ $electionResult->election_type_name }}</div>
                            <div class="result-stats">
                                <span class="stat-chip">
                                    <i class="fas fa-users"></i> Registered: {{ number_format($electionResult->total_voters) }}
                                </span>
                                <span class="stat-chip">
                                    <i class="fas fa-check-circle"></i> Valid: {{ number_format($electionResult->obtained_votes) }}
                                </span>
                                @if(($electionResult->total_ballot - $electionResult->total_voters) != 0 && ($electionResult->total_ballot > $electionResult->total_voters))
                                    <span class="stat-chip over-vote">
                                        <i class="fas fa-exclamation-triangle"></i> Over: +{{ number_format($electionResult->total_ballot - $electionResult->total_voters) }}
                                    </span>
                                @else
                                    <span class="stat-chip no-over-vote">
                                        <i class="fas fa-check"></i> No Over-Voting
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 mt-md-0 d-flex align-items-center" style="gap:8px;">
                            @if($electionResult->verify_by_constituency == 1)
                                <span class="status-badge status-confirmed"><i class="fas fa-lock mr-1"></i>Confirmed</span>
                            @else
                                <span class="status-badge status-pending"><i class="fas fa-clock mr-1"></i>Unconfirmed</span>
                            @endif
                            <a href="{{ route('Agent.viewResults', [$electionResult->election_start_up_id, $electionResult->id]) }}" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @if($electionResult->verify_by_constituency != 1)
                                <a href="{{ route('Agent.Home', [$electionResult->election_start_up_id, $electionResult->id]) }}" class="btn-action btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox d-block"></i>
                    <p class="font-weight-bold">No results submitted yet</p>
                    <p class="text-muted">Go to <a href="{{ route('Agent.election') }}" style="color:var(--ndc-green); font-weight:600;">Capture Result</a> to enter your first election result.</p>
                </div>
            @endif
        </div>
    </div>
</div>


@endsection
@section('script')
@endsection
