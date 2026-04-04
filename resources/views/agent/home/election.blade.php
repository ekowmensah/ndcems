@extends('layouts.app_agent')
@section('css')
<style>
    .election-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .election-card .card-header {
        background: linear-gradient(135deg, #006B3F 0%, #004D2E 100%);
        color: #fff;
        padding: 18px 20px;
        border: none;
    }
    .election-card .card-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
    }
    .election-card .card-body {
        padding: 24px 20px;
    }
    .election-select {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 1rem;
        height: auto;
        transition: border-color 0.3s;
        -webkit-appearance: none;
    }
    .election-select:focus {
        border-color: #006B3F;
        box-shadow: 0 0 0 0.15rem rgba(0,107,63,0.15);
    }
    .btn-ndc-green {
        background: linear-gradient(135deg, #006B3F, #004D2E);
        border: none;
        color: #fff;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.3px;
        transition: all 0.3s;
        width: 100%;
    }
    .btn-ndc-green:hover, .btn-ndc-green:focus {
        background: linear-gradient(135deg, #004D2E, #1a1a1a);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,107,63,0.3);
    }
    .btn-ndc-outline {
        border: 2px solid #e0e0e0;
        background: transparent;
        color: #555;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s;
        width: 100%;
    }
    .btn-ndc-outline:hover {
        border-color: #CE1126;
        color: #CE1126;
    }
    .instruction-text {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 20px;
    }
    @media (max-width: 576px) {
        .election-card .card-body { padding: 18px 16px; }
        .election-select { font-size: 16px; }
    }
</style>
@endsection
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">

            <div class="text-center mb-4 mt-3">
                <i class="fas fa-vote-yea fa-2x" style="color: #006B3F;"></i>
                <h4 class="mt-2 font-weight-bold">Capture Election Results</h4>
                <h6>Want to Update Captured Results? <a href="../agent/results" >Click Here</a></h6>
                <p class="instruction-text">Select an active election below to begin entering results from your polling station.</p>
            </div>

            <div class="card election-card">
                <div class="card-header">
                    <h3><i class="fas fa-ballot-check mr-2"></i>Select Election</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('Agent.electionPost') }}" method="POST" id="terminal-form">
                        @csrf

                        <div class="form-group">
                            <label for="election_start_update" class="font-weight-bold" style="font-size:0.85rem; text-transform:uppercase; letter-spacing:0.5px; color:#333;">
                                <i class="fas fa-list-ul mr-1"></i> Active Elections
                            </label>
                            <select class="form-control election-select" name="election_start_update" id="election_start_update" required>
                                <option value="">-- Choose an Election --</option>
                                @foreach ($electionStartupDetail as $electionStartup)
                                    <option value="{{ $electionStartup->id }}">{{ $electionStartup->election_name }} &mdash; {{ $electionStartup->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6">
                                <button type="reset" class="btn btn-ndc-outline" id="reset">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                            <div class="col-6">
                                <button type="submit" class="btn btn-ndc-green" id="submit">
                                    <i class="fas fa-arrow-right mr-1"></i> Proceed
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
@section('script')
@endsection
