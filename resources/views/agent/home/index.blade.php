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
    .station-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        overflow: hidden;
        margin-bottom: 16px;
    }
    .station-card .card-header {
        background: linear-gradient(135deg, var(--ndc-green), var(--ndc-green-dark));
        color: #fff;
        padding: 14px 16px;
        border: none;
        cursor: pointer;
    }
    .station-card .card-header h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .station-card .card-body { padding: 16px; }
    .station-detail {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .station-detail .detail-item {
        flex: 1 1 calc(50% - 8px);
        min-width: 140px;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.82rem;
    }
    .station-detail .detail-item .label {
        color: #888;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: block;
    }
    .station-detail .detail-item .value {
        color: var(--ndc-black);
        font-weight: 600;
        font-size: 0.85rem;
    }
    .results-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        overflow: hidden;
    }
    .results-card .card-header {
        background: #fff;
        border-bottom: 3px solid var(--ndc-green);
        padding: 14px 16px;
    }
    .results-card .card-header h5 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--ndc-black);
    }
    .candidate-row {
        display: flex;
        align-items: center;
        padding: 10px 16px;
        border-bottom: 1px solid #f0f0f0;
        gap: 10px;
    }
    .candidate-row:last-of-type { border-bottom: none; }
    .candidate-row .sn {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--ndc-green);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .candidate-row .candidate-info {
        flex: 1;
        min-width: 0;
    }
    .candidate-row .candidate-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--ndc-black);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .candidate-row .party-tag {
        display: inline-block;
        background: #e8f5e9;
        color: var(--ndc-green);
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        letter-spacing: 0.3px;
    }
    .candidate-row .vote-input {
        width: 100px;
        flex-shrink: 0;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 10px 8px;
        font-size: 16px;
        font-weight: 700;
        text-align: center;
        transition: border-color 0.3s;
    }
    .candidate-row .vote-input:focus {
        border-color: var(--ndc-green);
        box-shadow: 0 0 0 0.15rem rgba(0,107,63,0.15);
        outline: none;
    }
    .summary-section {
        background: #f8f9fa;
        border-radius: 0 0 12px 12px;
        padding: 16px;
    }
    .summary-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e8e8e8;
    }
    .summary-row:last-child { border-bottom: none; }
    .summary-row .summary-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        color: #444;
    }
    .summary-row .summary-label .badge-letter {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 800;
        color: #fff;
    }
    .badge-a { background: var(--ndc-green); }
    .badge-b { background: var(--ndc-red); }
    .badge-c { background: var(--ndc-black); }
    .summary-row .summary-input {
        width: 100px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 8px;
        font-size: 16px;
        font-weight: 700;
        text-align: center;
        background: #fff;
    }
    .summary-row .summary-input:focus {
        border-color: var(--ndc-green);
        box-shadow: 0 0 0 0.15rem rgba(0,107,63,0.15);
        outline: none;
    }
    .summary-row .summary-input[disabled],
    .summary-row .summary-input[readonly] {
        background: #e9ecef;
        color: #333;
    }
    .action-bar {
        position: sticky;
        bottom: 0;
        background: #fff;
        border-top: 1px solid #e0e0e0;
        padding: 12px 16px;
        display: flex;
        gap: 10px;
        box-shadow: 0 -2px 8px rgba(0,0,0,0.06);
        border-radius: 12px 12px 0 0;
        z-index: 100;
    }
    .btn-ndc-submit {
        flex: 2;
        background: linear-gradient(135deg, var(--ndc-green), var(--ndc-green-dark));
        border: none;
        color: #fff;
        padding: 14px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.3s;
    }
    .btn-ndc-submit:hover, .btn-ndc-submit:focus {
        background: linear-gradient(135deg, var(--ndc-green-dark), var(--ndc-black));
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,107,63,0.3);
    }
    .btn-ndc-reset {
        flex: 1;
        border: 2px solid #e0e0e0;
        background: #fff;
        color: #555;
        padding: 14px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s;
    }
    .btn-ndc-reset:hover { border-color: var(--ndc-red); color: var(--ndc-red); }

    @media (max-width: 576px) {
        .candidate-row { flex-wrap: wrap; padding: 12px; }
        .candidate-row .vote-input { width: 100%; margin-top: 6px; }
        .candidate-row .candidate-info { flex: 1 1 calc(100% - 40px); }
        .station-detail .detail-item { flex: 1 1 100%; }
        .summary-row { flex-wrap: wrap; gap: 6px; }
        .summary-row .summary-input { width: 100%; }
    }
</style>
@endsection
@section('content')

<div class="container pb-5">
    <form action="{{ route('Agent.CaptureResult', $election_start_up) }}" method="POST" id="terminal-form">
        @csrf

        {{-- Station Info - Collapsible --}}
        <div class="card station-card">
            <div class="card-header" data-toggle="collapse" data-target="#stationDetails" aria-expanded="false">
                <h5>
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    {{ $user->PollingStation_name ?? 'Polling Station' }}
                    <small class="float-right"><i class="fas fa-chevron-down"></i></small>
                </h5>
            </div>
            <div class="collapse" id="stationDetails">
                <div class="card-body">
                    <div class="station-detail">
                        <div class="detail-item">
                            <span class="label">Election</span>
                            <span class="value">{{ $electionStartupDetail->election_name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Type</span>
                            <span class="value">{{ $electionStartupDetail->name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Station Code</span>
                            <span class="value">{{ $user->PollingStation_Id }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Role</span>
                            <span class="value">{{ $user->user_type_name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Region</span>
                            <span class="value">{{ $user->region_name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Constituency</span>
                            <span class="value">{{ $user->constituency_name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Electoral Area</span>
                            <span class="value">{{ $user->ElectoralArea_name }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Registered Voters</span>
                            <span class="value">{{ number_format($user->total_voters ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Candidates & Votes --}}
        <div class="card results-card">
            <div class="card-header">
                <h5><i class="fas fa-pencil-alt mr-2" style="color:var(--ndc-green)"></i>Enter Votes</h5>
            </div>

            <div class="card-body p-0">
                @if($electionResult && isset($electionResult) && count($electionResult)!=0)
                    @foreach($electionResult as $key => $_electionResult)
                    <div class="candidate-row">
                        <div class="sn">{{ $key + 1 }}</div>
                        <div class="candidate-info">
                            <div class="candidate-name">{{ $_electionResult->first_name }} {{ $_electionResult->last_name }}</div>
                            <span class="party-tag">{{ $_electionResult->party_initial }}</span>
                        </div>
                        <input value="{{ $_electionResult->party_election_result_obtained_vote }}"
                               name="party[][{{ $_electionResult->party_election_result_party_id }}][{{ $_electionResult->party_election_result_candidate_id }}]"
                               type="number" class="vote-input party_vote" inputmode="numeric" pattern="[0-9]*"
                               placeholder="0" required>
                    </div>
                    @endforeach
                @else
                    @foreach($parties as $key => $party)
                    <div class="candidate-row">
                        <div class="sn">{{ $key + 1 }}</div>
                        <div class="candidate-info">
                            <div class="candidate-name">{{ $party->first_name }} {{ $party->last_name }}</div>
                            <span class="party-tag">{{ $party->party_initial }}</span>
                        </div>
                        <input name="party[][{{ $party->political_party_id }}][{{ $party->candidate_id }}]"
                               type="number" class="vote-input party_vote" inputmode="numeric" pattern="[0-9]*"
                               placeholder="0" required>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- Summary Section --}}
            <div class="summary-section">
                <div class="summary-row">
                    <div class="summary-label">
                        <span class="badge-letter badge-a">A</span> Total Valid Votes
                    </div>
                    @if($electionResult && count($electionResult)!=0)
                        <input value="{{ $electionResult->toArray()[0]['total_ballot'] - $electionResult->toArray()[0]['total_rejected_ballot'] }}"
                               type="text" class="summary-input" id="total1111" name="total1111" placeholder="0" disabled>
                    @else
                        <input type="text" class="summary-input" id="total1111" name="total1111" placeholder="0" readonly>
                    @endif
                </div>
                <div class="summary-row">
                    <div class="summary-label">
                        <span class="badge-letter badge-b">B</span> Rejected Ballots
                    </div>
                    @if($electionResult && count($electionResult)!=0)
                        <input value="{{ $electionResult->toArray()[0]['total_rejected_ballot'] }}"
                               type="number" class="summary-input total_rejected_ballot" name="total_rejected_ballot"
                               inputmode="numeric" placeholder="0" required>
                    @else
                        <input type="number" class="summary-input total_rejected_ballot" name="total_rejected_ballot"
                               inputmode="numeric" placeholder="0" required>
                    @endif
                </div>
                <div class="summary-row">
                    <div class="summary-label">
                        <span class="badge-letter badge-c">C</span> Total Ballot Box
                    </div>
                    @if($electionResult && count($electionResult)!=0)
                        <input value="{{ $electionResult->toArray()[0]['total_ballot'] }}"
                               type="text" class="summary-input" id="total_total" name="total_total" placeholder="0" disabled>
                    @else
                        <input type="text" class="summary-input" id="total_total" name="total_total" placeholder="0" disabled>
                    @endif
                </div>
            </div>
        </div>

        {{-- Hidden fields --}}
        @if($electionResult && count($electionResult)!=0 && isset($electionResult->toArray()[0]['id']))
            <input type="hidden" name="election_result_id" value="{{ $electionResult->toArray()[0]['id'] }}">
        @endif
        <input type="hidden" name="customer_ip" value="{{ request()->ip() }}">

        {{-- Sticky Action Bar --}}
        <div class="action-bar">
            <button type="reset" class="btn btn-ndc-reset" id="reset">
                <i class="fas fa-undo"></i>
            </button>
            <button type="submit" class="btn btn-ndc-submit" id="submit">
                <i class="fas fa-paper-plane mr-1"></i> Submit Results
            </button>
        </div>
    </form>
</div>

@endsection
@section('script')
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>



    $( document ).ready(function() {
        var total_voters = {!! json_encode($user->total_voters) !!};
        total_voters = parseInt(total_voters)

        $('body').on('keyup', '.party_vote', function() {
            var quantity = 0;
            quantity = parseInt($(".total_rejected_ballot").val())+parseInt($('#total1111').val());
            if(total_voters<quantity){
                swal("Opss!", "Over Voting Identified!", "error");

                return false
            }

        });
        $('body').on('keyup', '.total_rejected_ballot', function() {
            var quantity = 0;
            quantity = parseInt($(".total_rejected_ballot").val())+parseInt($('#total1111').val());
            if(total_voters<quantity){
                swal("Oppss!", "Over Voting Identified!", "error");

                return false
            }

        });
    });

$('body').on('keyup', '.party_vote', function() {

    var total=0;
    $(".party_vote").each(function(){
        quantity = parseInt($(this).val());
        if (!isNaN(quantity)) {
            total += quantity;
        }
    });
    $('#total1111').val(total);


        var quantity = 0;
        quantity = parseInt($(".total_rejected_ballot").val())+parseInt($('#total1111').val());
        $('#total_total').val(quantity);

});



    $('body').on('keyup', '.total_rejected_ballot', function() {

        var quantity = 0;
        quantity = parseInt($(this).val())+parseInt($('#total1111').val());
        $('#total_total').val(quantity);

    });

</script>
@endsection
