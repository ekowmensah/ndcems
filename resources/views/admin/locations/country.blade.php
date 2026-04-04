@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Manage Countries <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('SuperAdmin.New.Country')}}"  style=" float:  right;" class="btn btn-success">Add New Country</a>
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Countries </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table">
              <thead>
                <tr>

                    <th>#</th>
                  <th>Country Code</th>
                  <th>Country Name</th>
                  <th>Total Constituency</th>
                  <th>Total Electoral Areas</th>
                  <th>Total Polling Station</th>
                  <th>Total Voters</th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($countries as $country)
                        <tr>
                        <th scope="row">#</th>
                            <td>{{$country->country_id}}</td>
                            <td>{{$country->name}}</td>
                            <td>{{$country->total_constituency}}</td>
                            <td>{{$country->total_electral}}</td>
                            <td>{{$country->total_polling}}</td>
                            <td>{{$country->total_voters}}</td>
                            <td>
                                <a href="{{route('SuperAdmin.countryEdit',$country->id)}}"   class="btn btn-success btn-xs">Edit</a>
                                <a onclick="return confirm('Delete entry?')" href="{{route('SuperAdmin.countryDelete',$country->id)}}"  class="btn btn-danger btn-xs">Delete</a>
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
