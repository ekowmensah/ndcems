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
    .view-station-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .view-station-card .card-header {
        background: linear-gradient(135deg, var(--ndc-green), var(--ndc-green-dark));
        color: #fff;
        padding: 14px 16px;
        border: none;
    }
    .view-station-card .card-header h5 { margin: 0; font-size: 0.95rem; font-weight: 600; }
    .view-station-card .card-body { padding: 16px; }
    .detail-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .detail-grid .detail-chip {
        flex: 1 1 calc(50% - 8px);
        min-width: 140px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px 12px;
    }
    .detail-chip .chip-label {
        color: #888;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }
    .detail-chip .chip-value {
        color: var(--ndc-black);
        font-weight: 600;
        font-size: 0.85rem;
    }
    .view-results-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .view-results-card .card-header {
        background: #fff;
        border-bottom: 3px solid var(--ndc-green);
        padding: 14px 16px;
    }
    .view-results-card .card-header h5 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--ndc-black); }
    .vote-row {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f0f0f0;
        gap: 12px;
    }
    .vote-row:last-of-type { border-bottom: none; }
    .vote-row .v-candidate {
        flex: 1;
        min-width: 0;
    }
    .vote-row .v-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--ndc-black);
    }
    .vote-row .v-party {
        display: inline-block;
        background: #e8f5e9;
        color: var(--ndc-green);
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
    }
    .vote-row .v-votes {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--ndc-green);
        min-width: 60px;
        text-align: right;
    }
    .summary-boxes {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 0 0 12px 12px;
    }
    .summary-box {
        flex: 1 1 calc(33.333% - 10px);
        min-width: 100px;
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .summary-box .sb-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #888;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .summary-box .sb-value {
        font-size: 1.3rem;
        font-weight: 800;
    }
    .sb-rejected .sb-value { color: var(--ndc-red); }
    .sb-valid .sb-value { color: var(--ndc-green); }
    .sb-total .sb-value { color: var(--ndc-black); }
    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, var(--ndc-green), var(--ndc-green-dark));
        color: #fff;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.3s;
        border: none;
    }
    .btn-back:hover { background: linear-gradient(135deg, var(--ndc-green-dark), var(--ndc-black)); color: #fff; text-decoration: none; }

    @media (max-width: 576px) {
        .detail-grid .detail-chip { flex: 1 1 100%; }
        .summary-box { flex: 1 1 100%; }
        .vote-row { flex-wrap: wrap; }
        .vote-row .v-votes { width: 100%; text-align: left; margin-top: 4px; }
    }
</style>
@endsection
@section('content')

<div class="container mt-3 pb-4">
    {{-- Back link --}}
    <a href="{{ route('Agent.results') }}" class="btn-back mb-3">
        <i class="fas fa-arrow-left"></i> Back to Results
    </a>

    {{-- Station Details --}}
    <div class="card view-station-card">
        <div class="card-header">
            <h5><i class="fas fa-map-marker-alt mr-2"></i>Polling Station Detail</h5>
        </div>
        <div class="card-body">
            <div class="detail-grid">
                <div class="detail-chip">
                    <span class="chip-label">Election</span>
                    <span class="chip-value">{{ $electionStartupDetail->election_name }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Type</span>
                    <span class="chip-value">{{ $electionStartupDetail->name }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Station Name</span>
                    <span class="chip-value">{{ $user->PollingStation_name }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Station Code</span>
                    <span class="chip-value">{{ $user->PollingStation_Id }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Region</span>
                    <span class="chip-value">{{ $user->region_name }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Constituency</span>
                    <span class="chip-value">{{ $user->constituency_name }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Electoral Area</span>
                    <span class="chip-value">{{ $user->ElectoralArea_name }}</span>
                </div>
                <div class="detail-chip">
                    <span class="chip-label">Logged In As</span>
                    <span class="chip-value">{{ $user->user_type_name }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Results Card --}}
    <div class="card view-results-card">
        <div class="card-header">
            <h5><i class="fas fa-chart-bar mr-2" style="color:var(--ndc-green)"></i>Votes Breakdown</h5>
        </div>
        <div class="card-body p-0">
            @if($electionResult && isset($electionResult) && count($electionResult) != 0)
                @foreach($electionResult as $_electionResult)
                <div class="vote-row">
                    <div class="v-candidate">
                        <div class="v-name">{{ $_electionResult->first_name }} {{ $_electionResult->last_name }}</div>
                        <span class="v-party">{{ $_electionResult->party_initial }}</span>
                    </div>
                    <div class="v-votes">{{ number_format($_electionResult->party_election_result_obtained_vote) }}</div>
                </div>
                @endforeach
            @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle mr-1"></i> No results available for this election.
                </div>
            @endif
        </div>

        @if($electionResult && isset($electionResult) && count($electionResult) != 0)
        <div class="summary-boxes">
            <div class="summary-box sb-valid">
                <div class="sb-label">Valid Votes</div>
                <div class="sb-value">{{ number_format($electionResult->toArray()[0]['obtained_votes']) }}</div>
            </div>
            <div class="summary-box sb-rejected">
                <div class="sb-label">Rejected</div>
                <div class="sb-value">{{ number_format($electionResult->toArray()[0]['total_rejected_ballot']) }}</div>
            </div>
            <div class="summary-box sb-total">
                <div class="sb-label">Total Ballot</div>
                <div class="sb-value">{{ number_format($electionResult->toArray()[0]['total_ballot']) }}</div>
            </div>
        </div>
        @endif
    </div>

    @if($electionResult && isset($electionResult) && count($electionResult) != 0)
    <div class="card view-results-card">
        <div class="card-header">
            <h5><i class="fas fa-camera mr-2" style="color:var(--ndc-red)"></i>Pink Sheet</h5>
        </div>
        <div class="card-body text-center">
            @if(!empty($electionResult->toArray()[0]['pink_sheet_path']))
                <p><a href="{{ route('Agent.ViewPinkSheet', $electionResult->toArray()[0]['id']) }}" target="_blank" rel="noopener">Open Full Image</a></p>
                <img src="{{ route('Agent.ViewPinkSheet', $electionResult->toArray()[0]['id']) }}" alt="Pink Sheet" style="max-width:100%;max-height:380px;border:1px solid #ddd;border-radius:8px;padding:4px;background:#fff;">
            @else
                <p class="text-muted mb-0">Pink sheet has not been uploaded yet.</p>
            @endif
        </div>
    </div>
    @endif
</div>


@endsection
@section('script')
@endsection
