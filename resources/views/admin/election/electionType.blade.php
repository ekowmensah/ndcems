@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Election Types <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('SuperAdmin.New.electionType')}}"  style=" float:  right;" class="btn btn-success">Create New Election Type</a>
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-8 col-md-offset-2">
        <div class="x_panel">
          <div class="x_title">
            <h2> Election </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($electionTypes as $UserType)
                        <tr>
                        <th scope="row">#</th>
                            <td>{{$UserType->name}}</td>

                            <td>
                                <a href="{{route('SuperAdmin.electionTypesEdit',$UserType->id)}}"   class="btn btn-success btn-xs">Edit</a>
                              {{--   <a href="{{route('SuperAdmin.electionTypesDelete',$UserType->id)}}" onclick="return confirm('Delete entry?')" class="btn btn-danger btn-xs">Delete</a> --}}
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
