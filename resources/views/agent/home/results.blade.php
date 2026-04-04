@extends('layouts.app_agent')
@section('content')


                <div class="container-fluid">
                        <br>
                        <div class="row">

                        <div class="col-md-12">
                            <div class="panel" >
                                <div class="panel-heading">

                                </div>
                                <div class="panel-body">
                                        <div class="col-md-12">
                                                <br>
                                                <table class="table table-striped">
                                                        <thead>
                                                          <tr>
                                                            <th>Election Name</th>
                                                            <th>Election Type</th>
                                                            <th>Registered Voters</th>
                                                            <th>Valid Votes</th>
                                                            <th >Over Voting</th>
                                                            <th></th>
                                                          </tr>
                                                        </thead>
                                                        <tbody>
                                                                @foreach ($electionResults as $electionResult)
                                                          <tr>
                                                            <td>  {{$electionResult->election_name}}</td>
                                                            <td>  {{$electionResult->election_type_name}}</td>
                                                            <td>  {{number_format($electionResult->total_voters)}}</td>
                                                            <td>  {{number_format($electionResult->obtained_votes)}}</td>

                                                                @if(($electionResult->total_ballot - $electionResult->total_voters)!=0 && ($electionResult->total_ballot > $electionResult->total_voters))
                                                                    <td class="fa fa-close" style="color:red"> {{number_format($electionResult->total_ballot-$electionResult->total_voters)}} </td>
                                                                @else
                                                                    <td> <span class="fa fa-check-square-o" style="color:green;font-size:16px"> No </span></td>
                                                                @endif


                                                            <td>
                                                                    <a style="color:white"  href="{{route('Agent.viewResults',[$electionResult->election_start_up_id,$electionResult->id])}}"> <span class="fa fa-eye" style="color:#0000CD"title="View Results"> </span></a>

                                                                @if($electionResult->verify_by_constituency !=1)

                                                                    <a style="color:white"  href="{{route('Agent.Home',[$electionResult->election_start_up_id,$electionResult->id])}}"> <span class="fa fa-edit" style="color:#FF4500"title="Edit/Update Results"> </span></a>
                                                                @endif
                                                            </td>
                                                          </tr>
                                                          @endforeach
                                                        </tbody>
                                                      </table>
                                            </div>
                                   {{--  @foreach ($electionResults as $electionResult)
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                    @endforeach --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>





@endsection
@section('script')
<style>
    td {
   /* padding:0 !important; margin:0 !important;
   line-height: 4px */

}
</style>
@endsection
