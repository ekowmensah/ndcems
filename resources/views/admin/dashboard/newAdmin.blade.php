@extends('admin.layouts.app')

@section('content')
                <div class="">
                    <div class="clearfix"></div>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Create New Admin User</h2>
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
                    <form method="post" id="input-form" data-parsley-validate="" action="{{route('SuperAdmin.newAdminPost')}}" class="form-horizontal form-label-left" novalidate="">
                    {{ csrf_field() }}
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role_name">Full Name <span class="required">*</span>
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="text" name="name" value="" id="fname"  class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role_guard_name">Email Address
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="email" name="email" value="" id="email"  class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="role_guard_name">Password
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                          <input type="password" name="password" value="" id="password"  class="form-control col-md-7 col-xs-12">
                        </div>
                      </div>

                    <div class="form-group" id="site-gateways"></div>
                      <div class="ln_solid"></div>
                      <div class="form-group">
                        <div class="col-md-4 col-sm-4 col-xs-12 col-md-offset-3 pull-right">
                          <input type="submit" class="btn btn-success" value="Save User">
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
