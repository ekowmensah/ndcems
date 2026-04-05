@extends('layouts.app_director')
@section('content')


        <!-- MAIN CONTENT -->
         <div >
                <div class="container-fluid">
                    <br>
                    <h3 class="page-title">Result Detail</h3>
                    <div class="row">

                        {{-- <div class="col-md-12">
                            <div class="panel" style="background-color: aliceblue;">
                                <div class="panel-heading">
                                    <h1 class="panel-title"><strong>My Polling Station Detail</strong></h1>
                                </div>
                                <div class="panel-body">

                                </div>
                            </div>
                        </div> --}}

                            <div class="col-md-12">
                                    <div class="panel">
                                        {{-- <div class="panel-heading">
                                            <h3 class="panel-title">Details</h3>
                                        </div> --}}
                                        <div class="panel-body" style="border: 6px solid #f1f8ff">

                                                <div class="col-md-4">
                                                        <strong>Name Of Candidate</strong>
                                                </div>
                                                <div class="col-md-4">
                                                        <strong>Party Initial</strong>
                                                </div>
                                                <div class="col-md-4">
                                                        <strong>Votes Obtained </strong>
                                                </div>
                                                <div class="col-md-12">
                                                    <br>
                                                </div>
                                                @if($electionResult && isset($electionResult) && count($electionResult)!=0)
                                                    @foreach($electionResult as $_electionResult)
                                                    <div class="col-md-12">
                                                            <div class="col-md-4">
                                                                    <strong>{{$_electionResult->first_name}} {{$_electionResult->last_name}}</strong>
                                                            </div>
                                                            <div class="col-md-4">
                                                                    <strong>{{$_electionResult->party_initial}}</strong>
                                                            </div>
                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>
                                                                    {{$_electionResult->party_election_result_obtained_vote}}

                                                                            </label>

                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>
                                                    </div>
                                                    @endforeach

                                                    <div class="col-md-12">

                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Rejected Vote</label>
                                                                           <p style="background: aliceblue;text-align: center;"> {{$electionResult->toArray()[0]['total_rejected_ballot']}}</p>
                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>
                                                            <div class="col-md-4">

                                                            </div>
                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Obtained Votes</label>
                                                                            <p style="background: aliceblue;text-align: center;"> {{$electionResult->toArray()[0]['obtained_votes']}}</p>

                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>

                                                    </div>
                                                    <div class="col-md-12">
                                                            <div class="col-md-4">

                                                                </div>
                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Total Votes at Polling Station</label>
                                                                            <p style="background: aliceblue;text-align: center;"> {{$electionResult->toArray()[0]['total_ballot']}}</p>

                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>


                                                    </div>
                                                    <div class="col-md-12">
                                                            <div class="col-md-4"></div>
                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Pink Sheet</label>
                                                                            @if(!empty($electionResult->toArray()[0]['pink_sheet_path']))
                                                                                <p style="text-align: center;margin-bottom: 8px;">
                                                                                    <a href="{{ route('Director.ViewPinkSheet', $electionResult->toArray()[0]['id']) }}" target="_blank" rel="noopener">Open Full Image</a>
                                                                                </p>
                                                                                <img src="{{ route('Director.ViewPinkSheet', $electionResult->toArray()[0]['id']) }}" alt="Pink Sheet" style="max-width:100%;max-height:350px;border:1px solid #ddd;padding:4px;background:#fff;">
                                                                            @else
                                                                                <p style="background: aliceblue;text-align: center;">Not uploaded</p>
                                                                            @endif
                                                                    </div>
                                                            </div>
                                                            <div class="col-md-4"></div>
                                                    </div>
                                                @else

                                                @endif






                                        </div>

                                    </div>
                            </div>

                    </div>
                    <!-- end new -->
                    <!-- new 1 -->

                </div>
                <!-- end new 1 -->
                <!-- new 1 -->

            </div>





@endsection
@section('script')
@endsection
