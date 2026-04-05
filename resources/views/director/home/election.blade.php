@extends('layouts.app_director')

@section('content')
<div class="row">
    <div class="col-md-10 col-lg-8">
        <div class="card director-card">
            <div class="card-header">
                <h3 class="card-title">Capture or Update Constituency Result</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Select the election event and polling station to open result capture/edit mode. Confirmed results are protected from edits.
                </p>
                <form action="{{route('Director.electionPost')}}" method="POST" id="terminal-form">
                    @csrf
                    <div class="form-group">
                        <label class="font-weight-semibold">Election</label>
                        <select class="form-control" name="election_start_update" required>
                            <option value="">Choose election</option>
                            @foreach ($electionStartupDetail as $electionStartup)
                                <option value="{{$electionStartup->id}}">{{$electionStartup->election_name}} -- {{$electionStartup->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-semibold">Polling Station</label>
                        <select class="form-control" name="polling_station_id" required>
                            @foreach ($pollingStations as $electionStartup)
                                <option value="{{$electionStartup->id}}">{{$electionStartup->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex mt-4">
                        <button id="reset" class="btn btn-light mr-2" type="reset">Reset</button>
                        <button id="submit" type="submit" class="btn btn-success">
                            <i class="fas fa-arrow-right mr-1"></i> Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
