@extends('admin.layouts.app')

@section('content')
<div class="">
        <div class="clearfix"></div>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Election <small>Note : Start election only if you updated all candidates and polling stations</small></h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <table class="table">
                    <thead>
                      <tr>


                        <th class=" btn-primary">Country Name</th>
                        <th class=" btn-primary">Total Involved Constituency</th>
                        <th class=" btn-primary">Total Involved Elactral Area's</th>
                        <th class=" btn-primary">Total Involved Polling Station</th>
                        <th class=" btn-primary">Total Involved Voters</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach ($countries as $country)
                              <tr>
                                  <td class=" btn-warning">{{$country->name}}</td>
                                  <td class=" btn-warning">{{$country->total_constituency}}</td>
                                  <td class=" btn-warning">{{$country->total_electral}}</td>
                                  <td class=" btn-warning">{{$country->total_polling}}</td>
                                  <td class=" btn-warning">{{$country->total_voters}}</td>
                                </tr>
                        @endforeach
                    </tbody>
                  </table>
        <form action="{{route('SuperAdmin.electionNewPost')}}" method="post" id="input-form" data-parsley-validate="" action="" class="form-horizontal form-label-left" novalidate="">
        {{ csrf_field() }}
        @foreach ($countries as $country)
            {{-- <input type="hidden" name="election_type_id" value="{{$electionType->id}}"> --}}
            <input type="hidden" name="election_name" value="{{$country->name}}">
            <input type="hidden" name="total_constituency" value="{{$country->total_constituency}}">
            <input type="hidden" name="total_electral" value="{{$country->total_electral}}">
            <input type="hidden" name="total_polling" value="{{$country->total_polling}}">
            <input type="hidden" name="total_voters" value="{{$country->total_voters}}">
            @break
        @endforeach
        <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first-name">Election Type <span class="required">*</span>
                </label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <select name="election_type_id" class="form-control col-md-7 col-xs-12" required>
                        @foreach ($electionTypes as $electionType)
                            <option value="{{$electionType->id}}">{{$electionType->name}}</option>
                        @endforeach

                    </select>
                   {{--  <div class="alert alert-info alert-dismissible fade in" style="margin: 0 !important;" role="alert">

                      </div> --}}
                </div>
              </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role_name">Election Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
            <input type="text" name="election_name" id="fname"  class="form-control col-md-7 col-xs-12">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role_guard_name">Election Start and End Date Range
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">

                <fieldset>
                    <div class="control-group">
                        <div class="controls">
                            <div class="input-prepend input-group">
                                <span class="add-on input-group-addon"><i class="glyphicon glyphicon-calendar fa fa-calendar"></i></span>
                                <input  type="text" name="date" id="reservation-time" class="form-control" value="">
                            </div>
                        </div>
                    </div>
                </fieldset>
            </div>
          </div>


        <div class="form-group" id="site-gateways"></div>
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-4 col-sm-4 col-xs-12 col-md-offset-3 pull-right">
              <input type="submit" class="btn btn-success" value="Create Election">
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
</div>
@endsection

@section("script")
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
    <script>
        var start,end;
        $(document).ready(function() {
           /*  $('#reservation-time').daterangepicker({
                timePicker: true,
                timePickerIncrement: 30,
                locale: {
                    format: 'MM/DD/YYYY h:mm A'
                    //format: 'MM/DD/YYYY'
                }
            }, function(start, end, label) {
                start = start;
                end = end;
                console.log(start.toISOString(), end.toISOString());
            }) */

            $('#reservation-time').daterangepicker({
                startDate: "@if(isset($election->start)) {{Carbon\Carbon::parse($election->start)->format('m/d/Y')}} @else {{Carbon\Carbon::now()->format('m/d/Y')}} @endif",
                endDate: "@if(isset($election->start)) {{Carbon\Carbon::parse($election->end)->format('m/d/Y')}} @else {{Carbon\Carbon::now()->format('m/d/Y')}} @endif",
                ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                }
        });

        });

        $("#csv").click(function () {
            $('#gateway-form').submit();
//            var _token = $('input[name="_token"]').val();
//            $.ajax({
//                url: '/admin/ajaxreport',
//                type: 'POST',
//                data: {
//                    status : $('#filter-status').val(),
//                    terminal : $('#filter-terminal').val(),
//                    client : $('#filter-client').val(),
//                    _token : _token,
//                    date: $('#reservation-time').val()
//                },
//                success: function (result) {
//                    //alert('success');
//                }
//            });
        });


    </script>
        @endsection
