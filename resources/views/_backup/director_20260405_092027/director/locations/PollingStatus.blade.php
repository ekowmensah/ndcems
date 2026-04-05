@extends('layouts.app_director')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Manage Polling Station <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('Director.New.PollingStation')}}"  style=" float:  right;" class="btn btn-success">Add New Polling Station</a>
            </div>
    </div>


<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
                <select style="width:25vh; float:right" class="form-control filter" name="electoralarea_id" id="electoralarea_id"  required>

                        <option value="all" >Electoral Areas (All)<option>
                            @foreach ($countries as $electionType)
                                <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                            @endforeach
                </select>

                <br>
            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table" id="table">
              <thead>
                <tr>
                    <th>Polling Station Id</th>
                    <th>Polling Station Name</th>
                    <th>ElectoralArea Name</th>

                    <th>Constituency Name</th>
                  <th>Region Name</th>
                  <th>Country Name</th>
                  <th>Total Voter</th>
                  <th></th>
                  {{-- <th></th> --}}
                </tr>
              </thead>
              <tbody>
                  {{-- @foreach ($regions as $country)
                        <tr>

                            <td>{{$country->polling_station_id}}</td>
                            <td>{{$country->name}}</td>
                            <td>{{$country->ElectoralArea_name}}</td>
                            <td>{{$country->constituency_name}}</td>
                            <td>{{$country->region_name}}</td>
                            <td>{{$country->country_name}}</td>
                            <td>{{$country->total_voters}}</td>
                            <td>
                                <a href="{{route('SuperAdmin.constituencyEdit',$country->id)}}"   class="btn btn-success btn-xs">Edit</a>
                                <a href="{{route('SuperAdmin.ElectoralAreaDelete',$country->id)}}"  class="btn btn-danger btn-xs">Delete</a>
                            </td>
                        </tr>
                  @endforeach --}}


              </tbody>
            </table>

          </div>
        </div>
      </div>
</div>
</div>
@endsection

@section("script")
<script>
        $('document').ready(function(){
            $('select option')
            .filter(function() {
                return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
            })
            .remove();

        $("#region_id").on('change', '', function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
            $("#constituency_id").empty();
            var region_id = $("#region_id").val()
               // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                    var _token = $('input[name="_token"]').val();
                $.ajax({
                        type: "POST",
                        url: '{{route("Director.getConstituency")}}',
                        data: {region_id:region_id,_token:_token},
                        //dataType: "JSON",
                        success: function (result) {

                            $('#constituency_id')
                                    .append($("<option></option>")
                                                .attr("value","Select")
                                                .text("Select Constituency"));
                            $.each(result, function(key, value) {

                                $('#constituency_id')
                                    .append($("<option></option>")
                                                .attr("value",value.id)
                                                .text(value.name));
                            });
                           }
                    });
                });

                $("#constituency_id").on('change', '', function (e) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    $("#electoralarea_id").empty();
                    var constituency_id = $("#constituency_id").val()
                    // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                            var _token = $('input[name="_token"]').val();
                        $.ajax({
                                type: "POST",
                                url: '{{route("Director.getElectral")}}',
                                data: {constituency_id:constituency_id,_token:_token},
                                //dataType: "JSON",
                                success: function (result) {
                                    $('#electoralarea_id')
                                            .append($("<option></option>")
                                                        .attr("value","Select")
                                                        .text("Select Electoral Area"));
                                    $.each(result, function(key, value) {
                                        $('#electoralarea_id')
                                            .append($("<option></option>")
                                                        .attr("value",value.id)
                                                        .text(value.name));
                                    });
                                }
                            });
                        });
                })
                </script>

<script type="text/javascript">
    $(document).ready(function () {

       var data_table = $('#table').DataTable({
           //responsive: true,
       processing: true,
       serverSide: true,
       //"iDisplayLength": 100,
       "pageLength": 10,
       "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      stateSave: true,
       "order": [[ 1, "asc" ]],
       ajax: {url:'{!! route("Director.pollingStationAajax") !!}',
              data: function (d) {
              d.status = $('#filter-status').val();
              d.terminal = $('#filter-terminal').val();
              if($('#constituency_id').val()){
                d.constituency_id = $('#constituency_id').val();
              }else {
                d.constituency_id ="all"
              }

              if($('#region_id').val()){
                d.region_id = $('#region_id').val();
              }else{
                d.region_id = "all"
              }
              if($('#electoralarea_id').val()){
                d.electoralarea_id = $('#electoralarea_id').val();
              }else{
                d.electoralarea_id = "all"
              }



       }},
       "columnDefs": [
                  //  { "searchable": false, "targets": 4 },
                    { "searchable": false, "targets": 5 }
                ],
       columns: [


          { data: 'polling_station_id', name: 'pollingstation.polling_station_id' },
          { data: 'name', name: 'pollingstation.name' },
            { data: 'ElectoralArea_name', name: 'electoralarea.name' },
            { data: 'constituency_name', name: 'constituency.name' },
            { data: 'region_name', name: 'region.name' },
            { data: 'country_name', name: 'countries.name' },
            { data: 'total_voters', name: 'pollingstation.total_voters' },


          {
           mData:null,
           name:"id",
             "mRender": function (data) {

   var del = "{{ route('Director.PollingStationDelete',':number') }}"
                   del = del.replace(':number', data.id);

               var url = "{{ route('Director.PollingStationEdit',':number') }}";
                   url = url.replace(':number', data.id);
               return `
                    <a style="color:white" class="btn btn-primary btn-xs" href=${url}>Edit</a>
                      <a style="color:white" onclick="return confirm('Delete entry?')" class="btn btn-danger btn-xs" href=${del}>Delete</a>
                  `;
              }
           }
           ]
       });

       $('.filter').on('change', function (e) {

           data_table.draw();
       });
       $( ".filter" ).keyup(function() {
           data_table.draw();
       });
   });
   </script>
    <style>
        .current
        {
            background-color: #DDD !important;
        }
    </style>

        @endsection

