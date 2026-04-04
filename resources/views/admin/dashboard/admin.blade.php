@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">

        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('SuperAdmin.New.Admin')}}"  style=" float:  right;" class="btn btn-success">Create New Admin </a>
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Admin </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table">
              <thead>
                <tr>

                 <th>Admin name</th>
                  <th>Email</th>
                  <th>Registered At</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                  @foreach ($superAdmins as $country)
                        <tr>
                            <td>{{$country->name}}</td>
                            <td>{{$country->email}}</td>
                            <td>{{$country->created_at}}</td>
                            <td>
                                <a href="{{route('SuperAdmin.edit.Admin',$country->id)}}"   class="btn btn-success btn-xs">Edit</a>
                                <a href="{{route('SuperAdmin.delete.Admin',$country->id)}}" onclick="return confirm('Delete entry?')" class="btn btn-danger btn-xs">Delete</a>
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
{{-- <script>
    function init_flot_chart() {

if (typeof($.plot) === 'undefined') { return; }

console.log('init_flot_chart');
var data={!! str_replace(['"##','##"'],"",json_encode($array_data)) !!};

var newData=[
  []
];

var arr_data1 = [
    [gd(2012, 1, 1), 17],
    [gd(2012, 1, 2), 74],
    [gd(2012, 1, 3), 6],
    [gd(2012, 1, 4), 39],
    [gd(2012, 1, 5), 20],
    [gd(2012, 1, 6), 85],
    [gd(2012, 1, 7), 7]
];

var arr_data2 = [
    [gd(2012, 1, 1), 90],
    [gd(2012, 1, 2), 23],
    [gd(2012, 1, 3), 66],
    [gd(2012, 1, 4), 9],
    [gd(2012, 1, 5), 119],
    [gd(2012, 1, 6), 6],
    [gd(2012, 1, 7), 9]
];

var arr_data3 = [
    [0, 1],
    [1, 9],
    [2, 6],
    [3, 10],
    [4, 5],
    [5, 17],
    [6, 6],
    [7, 10],
    [8, 7],
    [9, 11],
    [10, 35],
    [11, 9],
    [12, 12],
    [13, 5],
    [14, 3],
    [15, 4],
    [16, 9]
];



var chart_plot_01_settings = {
    series: {
        lines: {
            show: false,
            fill: true
        },
        splines: {
            show: true,
            tension: 0.4,
            lineWidth: 1,
            fill: 0.4
        },
        points: {
            radius: 0,
            show: true
        },
        shadowSize: 2
    },
    grid: {
        verticalLines: true,
        hoverable: true,
        clickable: true,
        tickColor: "#d5d5d5",
        borderWidth: 1,
        color: '#fff'
    },
    colors: ["rgba(38, 185, 154, 0.38)", "rgba(3, 88, 106, 0.38)"],
    xaxis: {
        tickColor: "rgba(51, 51, 51, 0.06)",
        mode: "time",
        tickSize: [1, "day"],
        //tickLength: 10,
        axisLabel: "Date",
        axisLabelUseCanvas: true,
        axisLabelFontSizePixels: 12,
        axisLabelFontFamily: 'Verdana, Arial',
        axisLabelPadding: 10
    },
    yaxis: {
        ticks: 8,
        tickColor: "rgba(51, 51, 51, 0.06)",
    },
    tooltip: false
}

if ($("#chart_plot_01").length) {
    console.log('awais');

    $.plot($("#chart_plot_01"), [data], chart_plot_01_settings);
    //$.plot($("#chart_plot_01"), [arr_data1, arr_data2], chart_plot_01_settings);
}




}
$(document).ready(function() {
init_flot_chart();
init_chart_doughnut({!!json_encode($payment_method)!!});
$('#client-table').DataTable({
                processing: true,
        serverSide: true,
        ajax: '{!! route("admin.ajax.dataTable.client.dashboard") !!}',
        columns: [

            { data: 'client_id', name: 'transactions.client_id' },
            { data: 'name', name: 'clients.name' },
            { data: 'amount', name: 'amount' },
            { data: 'public_name', name: 'gateways.public_name' },



        ]
        });
});
</script>
    <style>
   /*  table tr td:nth-child(3) {
    text-align: right;
    } */
    .paginate_button {
        cursor: pointer;
    }
</style> --}}
        @endsection
