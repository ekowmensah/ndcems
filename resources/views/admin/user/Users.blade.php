@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-4" style=" float:  left;">
            <h3>Manager Type <small></small></h3>
        </div>
        <div class="col-md-12">
            <br>
                @foreach ($UserTypes as $UserType)
                    <a href="{{route('SuperAdmin.New.User',$UserType->id)}}"  style=" float:  right;" class="btn btn-success">Add {{$UserType->name}}</a>
                @endforeach
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Manager Types </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th>Added At</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($Users as $UserType)
                        <tr>
                        <th scope="row">#</th>
                            <td>{{$UserType->user_name}}</td>
                            <td>{{$UserType->user_type_name}}</td>
                            <td>{{$UserType->created_at}}</td>
                            <td>
                            <a href="{{route('SuperAdmin.UsersEdit',$UserType->user_id)}}"   class="btn btn-success btn-small">Edit</a>
                                <a href="#"  class="btn btn-danger btn-small">Delete</a>
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
