@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Manage Constituency <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('SuperAdmin.New.constituency')}}"  style=" float:  right;" class="btn btn-success">Add New Constituency</a>
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-8 col-sm-8 col-xs-8 col-md-offset-2">
        <div class="x_panel">
          <div class="x_title">
            <h2>Constituency </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table">
              <thead>
                <tr>
                  <th>Constituency Name</th>
                  <th>Region Name</th>
                  <th>Country Name</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($regions as $country)
                        <tr>

                            <td>{{$country->name}}</td>
                            <td>{{$country->region_name}}</td>
                            <td>{{$country->country_name}}</td>
                            <td>
                                <a href="{{route('SuperAdmin.constituencyEdit',$country->id)}}"   class="btn btn-success btn-xs">Edit</a>
                                <a href="{{route('SuperAdmin.constituencyDelete',$country->id)}}"  class="btn btn-danger btn-xs">Delete</a>
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
