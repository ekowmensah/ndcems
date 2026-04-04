@extends('layouts.app_agent')
@section('content')


        <!-- MAIN CONTENT -->
         <div >
                <div class="container-fluid">
                    <br>
                    <h3 class="page-title">Result Detail</h3>
                    <div class="row">

                        <div class="col-md-12">
                            <div class="panel" style="background-color: aliceblue;">
                                <div class="panel-heading">
                                    <h1 class="panel-title"><strong>My Polling Station Detail</strong></h1>
                                </div>
                                <div class="panel-body">
                                        <div class="col-md-4">
                                                <strong> Election  : </strong> {{$electionStartupDetail->election_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Election Type : </strong>{{$electionStartupDetail->name}}
                                        </div>
                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>

                                        <div class="col-md-4">
                                                <strong> Loged In as : </strong> {{$user->user_type_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Polling Station Name : </strong>{{$user->PollingStation_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Polling Station Code : </strong> {{$user->PollingStation_Id}}
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>
                                        <div class="col-md-4">
                                                <strong> Region : </strong> {{$user->region_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Constituency : </strong>{{$user->constituency_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Electoral Area  : </strong> {{$user->ElectoralArea_name}}
                                        </div>
                                </div>
                            </div>
                        </div>

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
