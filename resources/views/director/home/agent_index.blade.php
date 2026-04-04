@extends('layouts.app_director')
@section('content')


        <!-- MAIN CONTENT -->
        <form action="{{route('Director.CaptureResult',$election_start_up)}}" method="POST" id="terminal-form" class="form-horizontal form-label-left input_mask">
            @csrf
            <div >
                <div class="container-fluid">
                    <br>
                    <h3 class="page-title">Result Capture</h3>
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
                                              {{--   <strong> Loged In as : </strong> {{$polling->user_type_name}} --}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Polling Station Name : @if(isset($polling->name))</strong>{{$polling->name}}@endif
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Polling Station Code : @if(isset($polling->name))</strong> {{$polling->polling_station_id}}@endif
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>
                                        <input type="hidden" name="polling_station_id" value="{{$polling->id}}">
                                        <input type="hidden" name="electoral_area_id" value="{{@$polling->electoralarea_id}}">
                                        <input type="hidden" name="constituency_id" value="{{@$polling->constituency_id}}">
                                        <input type="hidden" name="region_id" value="{{@$polling->region_id}}">
                                        <input type="hidden" name="country_id" value="{{@$polling->country_id}}">
                                        @if($election_result_id=="null" && isset($electionResult->toArray()[0]['id']))

                                            <input type="hidden" name="election_result_id" value="{{$electionResult->toArray()[0]['id']}}">

                                        @else
                                            <input type="hidden" name="election_result_id" value="{{$election_result_id}}">
                                        @endif

                                        {{-- <div class="col-md-4">
                                                <strong> Region : </strong> {{$user->region_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Constituency : </strong>{{$user->constituency_name}}
                                        </div>
                                        <div class="col-md-4">
                                                <strong>Electoral Area  : </strong> {{$user->ElectoralArea_name}}
                                        </div> --}}
                                </div>
                            </div>
                        </div>

                            <div class="col-md-12">
                                    <div class="panel">
                                        {{-- <div class="panel-heading">
                                            <h3 class="panel-title">Details</h3>
                                        </div> --}}
                                        <div class="panel-body" style="border: 6px solid #f1f8ff">

                                                <div class="col-md-1" style="background-color:#86c1fb">
                                                        <strong>S/N</strong>
                                                </div>
                                                <div class="col-md-4" style="background-color:#86c1fb">
                                                        <strong>Name Of Candidate  </strong>
                                                </div>
                                                <div class="col-md-3" style="background-color:#86c1fb">
                                                        <strong>Party Initial</strong>
                                                </div>
                                                <div class="col-md-4" style="background-color:#86c1fb">
                                                        <strong>Votes Obtained </strong>
                                                </div>
                                                <div class="col-md-12">
                                                    <br>
                                                </div>
                                                @if($electionResult && isset($electionResult) && count($electionResult)!=0)
                                                    @foreach($electionResult as $key=> $_electionResult)
                                                    <div class="col-md-12">
                                                            <div class="col-md-1">
                                                                    <strong>{{$key+1}}</strong>
                                                            </div>
                                                            <div class="col-md-4">
                                                                    <strong>{{$_electionResult->first_name}} {{$_electionResult->last_name}}</strong>
                                                            </div>
                                                            <div class="col-md-3">
                                                                    <strong>{{$_electionResult->party_initial}}</strong>
                                                            </div>
                                                            <div class="col-md-4">
                                                                    <div class="form-group">

                                                                            <input value="{{$_electionResult->party_election_result_obtained_vote}}"  name="party[][{{$_electionResult->party_election_result_party_id}}][{{$_electionResult->party_election_result_candidate_id}}]" type="number" class="form-control has-feedback-left party_vote" tabindex="1" data-required-error=""  id="first_name" placeholder="Obtained Votes" required>

                                                                        </div>
                                                            </div>
                                                    </div>
                                                    @endforeach

                                                    {{-- <div class="col-md-12">
                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Rejected Vote</label>
                                                                            <input value="{{$electionResult->toArray()[0]['total_rejected_ballot']}}" type="text" class="form-control has-feedback-left" tabindex="1" data-required-error=""  name="total_rejected_ballot"  placeholder="Rejected Vote" required>
                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>
                                                    </div> --}}
                                                    <div class="col-md-1" >
                                                            <strong>A</strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <strong>Total Valid Votes  </strong>
                                                    </div>
                                                    <div class="col-md-3" >
                                                            <strong></strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <input value="{{$electionResult->toArray()[0]['total_ballot'] - $electionResult->toArray()[0]['total_rejected_ballot']}}"  type="text" class="form-control has-feedback-left" tabindex="1" data-required-error="" id="total1111"  name="total1111"  placeholder="Total Vote" disabled>

                                                    </div>
                                                    <div class="col-md-12" >
                                                          <br>
                                                    </div>
                                                    <div class="col-md-1" >
                                                            <strong>B</strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <strong >Rejected Ballots</strong>
                                                    </div>
                                                    <div class="col-md-3" >

                                                            <strong ></strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <input value="{{$electionResult->toArray()[0]['total_rejected_ballot']}}" type="text" class="form-control has-feedback-left total_rejected_ballot" tabindex="1" data-required-error=""  name="total_rejected_ballot"  placeholder="Rejected Vote" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                            <br>
                                                      </div>
                                                      <div class="col-md-1" >
                                                              <strong>C</strong>
                                                      </div>
                                                      <div class="col-md-4" >
                                                              <strong>Total Vote In Ballot Box </strong>
                                                      </div>
                                                      <div class="col-md-3" >
                                                              <strong></strong>
                                                      </div>
                                                      <div class="col-md-4" >
                                                            <input value="{{$electionResult->toArray()[0]['total_ballot']}}"  type="text" class="form-control has-feedback-left" tabindex="1" data-required-error="" id="total_total"  name="total_total"  placeholder="Total Vote" disabled>

                                                      </div>
                                                    {{-- <div class="col-md-12">

                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Total Votes at Polling Station</label>
                                                                            <input value="{{$electionResult->toArray()[0]['total_ballot']}}" type="text" class="form-control has-feedback-left" tabindex="1" data-required-error=""  name="total_ballot" placeholder="Total  Votes" required>
                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>


                                                    </div> --}}
                                                @else
                                                    @foreach($parties as $key => $party)
                                                    {{-- <input type="hidden" name="candidate_id[]" value="{{$party->candidate_id}}">
                                                    <input type="hidden" name="political_party_id[]" value="{{$party->political_party_id}}"> --}}
                                                    <div class="col-md-12">
                                                            <div class="col-md-1">
                                                                    <strong>{{$key+1}}</strong>
                                                            </div>
                                                            <div class="col-md-4">
                                                                    <strong>{{$party->first_name}} {{$party->last_name}}</strong>
                                                            </div>
                                                            <div class="col-md-3">
                                                                    <strong>{{$party->party_initial}}</strong>
                                                            </div>
                                                            <div class="col-md-4">
                                                                    <div class="form-group">

                                                                            <input  name="party[][{{$party->political_party_id}}][{{$party->candidate_id}}]" type="text" class="form-control has-feedback-left party_vote" tabindex="1" data-required-error=""  id="first_name" placeholder="Obtained Votes" required>

                                                                        </div>
                                                            </div>

                                                    </div>
                                                    @endforeach

                                                    {{-- <div class="col-md-12">

                                                            <div class="col-md-4">
                                                                    <div class="form-group">
                                                                            <label>Rejected Vote</label>
                                                                            <input type="text" class="form-control has-feedback-left" tabindex="1" data-required-error=""  name="total_rejected_ballot"  placeholder="Rejected Vote" required>
                                                                            <div class="help-block with-errors"></div>
                                                                        </div>
                                                            </div>

                                                    </div> --}}

                                                    <div class="col-md-1" >
                                                            <strong>A</strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <strong>Total Valid Votes  </strong>
                                                    </div>
                                                    <div class="col-md-3" >
                                                            <strong></strong>
                                                    </div>

                                                    <div class="col-md-4" >
                                                            <input  type="text" @if(isset($electionResult->toArray()[0]['obtained_votes'])) value="{{$electionResult->toArray()[0]['obtained_votes']}}" @endif class="form-control has-feedback-left" tabindex="1" data-required-error="" id="total1111"  name="total1111"  placeholder="Total Vote" readonly>

                                                    </div>
                                                    <div class="col-md-12" >
                                                          <br>
                                                    </div>
                                                    <div class="col-md-1" >
                                                            <strong>B</strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <strong >Rejected Ballots</strong>
                                                    </div>
                                                    <div class="col-md-3" >

                                                            <strong ></strong>
                                                    </div>
                                                    <div class="col-md-4" >
                                                            <input  type="text" class="form-control has-feedback-left total_rejected_ballot" tabindex="1" data-required-error=""  name="total_rejected_ballot"  placeholder="Rejected Vote" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                            <br>
                                                      </div>
                                                      <div class="col-md-1" >
                                                              <strong>C</strong>
                                                      </div>
                                                      <div class="col-md-4" >
                                                              <strong>Total Vote In Ballot Box </strong>
                                                      </div>
                                                      <div class="col-md-3" >
                                                              <strong></strong>
                                                      </div>
                                                      <div class="col-md-4" >
                                                            <input   type="text" class="form-control has-feedback-left" tabindex="1" data-required-error="" id="total_total"  name="total_total"  placeholder="Total Vote" disabled>

                                                      </div>

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
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <h3 class="panel-title"></h3>
                        </div>
                        <div class="panel-body">



                            <div class="form-group">
                                <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-4">
                                <input type="hidden" name="customer_ip" value="{{request()->ip()}}">
                                    <button id="reset" class="btn btn-primary" type="reset">Reset</button>
                                    <button id="submit" type="submit" class="btn btn-success"> <i id="submitIcon"> </i> Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>




@endsection
@section('script')
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>



   $( document ).ready(function() {
        var total_voters = {!! json_encode(@$pollingStation->total_voters) !!};
        total_voters = parseInt(total_voters)

        $('body').on('keyup', '.party_vote', function() {
            var quantity = 0;
            quantity = parseInt($(".total_rejected_ballot").val())+parseInt($('#total1111').val());
            if(total_voters<quantity){
                swal("Oppss!", "Over Voting Identified!", "error");

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
