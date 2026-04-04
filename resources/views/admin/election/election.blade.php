@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  right;">
            <br>
            <a href="{{route('SuperAdmin.electionNew')}}"  style=" float:  right;" class="btn btn-success">Add New Election</a>

        </div>

    </div>


</div>
<div class="clearfix"></div>
<div class="row">
<br>

    <div class="col-md-12">
            <div class="x_panel">
            <h2> Election </h2>

          <div class="x_title">
            <div class="col-md-6">

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">
            <table class="table">
              <thead>
                <tr>
                        <th>Election Name</th>
                    <th>Election Type</th>
                  <th>Status</th>
                  <th></th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($electionTypes as $UserType)
                        <tr>
                                <td>{{$UserType->election_name}}</td>
                            <td>{{$UserType->name}}</td>
                            <td>
                                @if($UserType->status && $UserType->status ===1 )
                                   <span style="background-color:green;color:white"> Already Started</span>
                                @elseif($UserType->status ===0 && $UserType->status !== null)
                                    <span style="background-color:red;color:white"> Stoped</span>
                                @else
                                    <span style="background-color:blue;color:white"> Not Started Yet</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{route('SuperAdmin.electionDetail',$UserType->election_startup_detail_id)}}"   class="btn btn-success btn-xs">  Edit</a>
                              {{--   <a href="{{route('SuperAdmin.electionTypesDelete',$UserType->id)}}" onclick="return confirm('Delete entry?')" class="btn btn-danger btn-xs">Delete</a> --}}
                            </td>
                            <td>
                                    @if($UserType->status === 0 && $UserType->status !== null)
                                        <a href="{{route('SuperAdmin.electionDetailTougle',[$UserType->election_startup_detail_id,1])}}"   class="btn btn-info btn-xs"> Start</a>
                                    @elseif($UserType->status ==1 )
                                        <a href="{{route('SuperAdmin.electionDetailTougle',[$UserType->election_startup_detail_id,0])}}"   class="btn btn-danger btn-xs"> Stop</a>
                                    @elseif($UserType->status == null )
                                        <a href="{{route('SuperAdmin.electionDetail',$UserType->election_startup_detail_id)}}"   class="btn btn-info btn-xs"> Start</a>
                                    @endif
                            </td>
                        </tr>
                  @endforeach


              </tbody>
            </table>

          </div>
        </div>
      </div>
</div>
@endsection

@section("script")

        @endsection
