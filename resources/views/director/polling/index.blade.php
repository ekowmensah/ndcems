@extends('layouts.app_director')
@section('content')
                <div class="container-fluid">
                        <br>
                        <div class="row">

                        <div class="col-md-12">
                            <div class="panel" >
                                <div class="panel-heading">
                    <a href="{{route('Director.New.User',$UserType->id)}}"  style=" float:  right;" class="btn btn-success">Add Polling Agent</a>
                    <br>

                                </div>
                                <div class="panel-body">
                                <div class="col-md-12">
                                    <table class="table" id="table">
                                        <thead>
                                          <tr>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Password</th>
                                            <th>Type</th>

                                            <th>Region</th>
                                            <th>Constituency</th>
                                            <th>Electoral Area</th>
                                            <th>Polling Station</th>

                                            <th>Added At</th>
                                            <th></th>
                                          </tr>
                                        </thead>
                                        <tbody>
                                            {{-- @foreach ($Users as $UserType)
                                                  <tr>
                                                  <th scope="row">#</th>
                                                      <td>{{$UserType->user_name}}</td>
                                                      <td>{{$UserType->user_type_name}}</td>
                                                      <td>{{$UserType->created_at}}</td>
                                                      <td>

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
                </div>
@endsection
@section('script')

<script type="text/javascript">
    $(document).ready(function () {
       $('select option')
               .filter(function() {
                   return !this.value || $.trim(this.value).length == 0 || $.trim(this.text).length == 0;
               })
               .remove();
       var data_table = $('#table').DataTable({
           //responsive: true,
       processing: true,
       serverSide: true,
       //"iDisplayLength": 100,
       "pageLength": 10,
       "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
      stateSave: true,
       "order": [[ 1, "asc" ]],
       ajax: {url:'{!! route("Director.pollingAgentAjax") !!}',
              data: function (d) {
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

                 if($('#polling_station_id').val()){
                   d.polling_station_id = $('#polling_station_id').val();
                 }else{
                   d.polling_station_id = "all"
                 }



       }},
       columns: [


          { data: 'user_name', name: 'users.name' },
          { data: 'username', name: 'users.username' },
          { data: 'secret', name: 'users.secret' },

          { data: 'user_type_name', name: 'user_type_name' },
            { data: 'region_name', name: 'region.name' },
          { data: 'constituency_name', name: 'constituency.name' },
          { data: 'ElectoralArea_name', name: 'ElectoralArea.name' },
          { data: 'PollingStation_name', name: 'PollingStation.name' },
          { data: 'created_at', name: 'users.created_at' },
            {
           mData:null,
           name:"user_id",
             "mRender": function (data) {

   var del = "{{ route('Director.UsersDelete',':number') }}"
                   del = del.replace(':number', data.user_id);


                var url = "{{ route('Director.UsersEdit',':number') }}";
                    url = url.replace(':number', data.user_id);
               return `
                      <a style="color:white" class="btn btn-primary btn-xs" href=${url}>Edit</a>
                      <a style="color:white" class="btn btn-danger btn-xs" onclick="return confirm('Delete entry?')" href=${del}>Delete</a>
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
                    url: '{{route("SuperAdmin.getConstituency")}}',
                    data: {region_id:region_id,_token:_token},
                    //dataType: "JSON",
                    success: function (result) {

                        $('#constituency_id')
                                .append($("<option></option>")
                                            .attr("value","all")
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
                            url: '{{route("SuperAdmin.getElectral")}}',
                            data: {constituency_id:constituency_id,_token:_token},
                            //dataType: "JSON",
                            success: function (result) {
                                $('#electoralarea_id')
                                        .append($("<option></option>")
                                                    .attr("value","all")
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


                    $("#electoralarea_id").on('change', '', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                $("#polling_station_id").empty();
                var electoralarea_id = $("#electoralarea_id").val()
                // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                        var _token = $('input[name="_token"]').val();
                    $.ajax({
                            type: "POST",
                            url: '{{route("SuperAdmin.getPollingStation")}}',
                            data: {electoralarea_id:electoralarea_id,_token:_token},
                            //dataType: "JSON",
                            success: function (result) {
                                $('#polling_station_id')
                                        .append($("<option></option>")
                                                    .attr("value","all")
                                                    .text("Select Polling station"));
                                $.each(result, function(key, value) {
                                    $('#polling_station_id')
                                        .append($("<option></option>")
                                                    .attr("value",value.id)
                                                    .text(value.name));
                                });
                            }
                        });
                    });
            })
            </script>


@endsection
