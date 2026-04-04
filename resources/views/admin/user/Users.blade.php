
@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-4" style=" float:  left;">
           {{--  <h3>Polling Agent <small></small></h3> --}}
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
            <h2>Manage Users </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">
            <select style="width:25vh; float:right" class="form-control filter" name="polling_station_id" id="polling_station_id"  required>


            </select>
            <select style="width:25vh; float:right" class="form-control filter" name="electoralarea_id" id="electoralarea_id"  required>


            </select>
    <select style="width:25vh; float:right" class="form-control filter" name="constituency_id" id="constituency_id"  required>


            </select>
    <select style="width:25vh;float:right" class="form-control filter" name="region_id" id="region_id"  required>
        <option value="all" >Regions (All)<option>
            @foreach ($electionTypes as $electionType)
            <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
             @endforeach
    </select>
    <br>
            <table class="table" id="table">
              <thead>
                <tr>
                        <th></th>
                    <th>Name</th>
                  <th>Username</th>

                  <th>Mobile</th>


                  <th>Type</th>

                  <th>Region</th>
                  <th>Constituency</th>
                  <th>Electoral Area</th>
                  <th>Polling Station</th>

                  {{-- <th>Added At</th> --}}
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
@endsection

@section("script")
<script type="text/javascript">
    $(document).ready(function () {
        var user_type_id = {!! json_encode($id) !!};
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
       ajax: {url:'{!! route("SuperAdmin.managementAgentAjax") !!}',
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
                 d.user_type_id = user_type_id;




       }},
       "columnDefs": [
            { "searchable": false, "targets": 9 }
        ],
        columns: [

        {
        mData:null,
        name:"photo",
        "mRender": function (data) {
            var matches = "/user_logo/"+data.photo;
                match = matches.replace('&quot;', '');
                match = match.replace('&quot;', '');
        return `
              <a href="${matches}" target="_blank">  <img src="${matches}" class="img-thumbnail" alt="${data.name}" width="50" height="50"></a>
            `;
        }
        },
          { data: 'user_name', name: 'users.name' },
          { data: 'username', name: 'users.username' },
          { data: 'phoneno', name: 'users.phoneno' },
          { data: 'user_type_name', name: 'user_type.name' },
            { data: 'region_name', name: 'region.name' },
          { data: 'constituency_name', name: 'constituency.name' },
          { data: 'ElectoralArea_name', name: 'ElectoralArea.name' },
          { data: 'PollingStation_name', name: 'PollingStation.name' },
         // { data: 'created_at', name: 'users.created_at' },
            {
           mData:null,
           name:"user_id",
             "mRender": function (data) {

   var del = "{{ route('SuperAdmin.UsersDelete',':number') }}"
                   del = del.replace(':number', data.user_id);


                var url = "{{ route('SuperAdmin.UsersEdit',':number') }}";
                    url = url.replace(':number', data.user_id);
               return `
                      <a style="color:blue" class="fa fa-edit" href=${url}></a>
                      <a style="color:red" class="fa fa-trash-o" onclick="return confirm('Delete entry?')" href=${del}></a>
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

        @if(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 0 ){
           //National Director
           /* setTimeout( function(){
                $("tr").each(function() {
                    $(this).find("th:eq(5)").remove();
                    $(this).find("th:eq(5)").remove();
                    $(this).find("th:eq(5)").remove();
                    $(this).find("th:eq(5)").remove();

                    $(this).find("td:eq(5)").remove();
                    $(this).find("td:eq(5)").remove();
                    $(this).find("td:eq(5)").remove();
                    $(this).find("td:eq(5)").remove();
                });
            }  , 500 ); */
        }
        @elseif(isset($NewUserTypes) && end($NewUserTypes)['index'] == 1 )
        {
             //region director
             /* setTimeout( function(){
                $("tr").each(function() {
                    $(this).find("th:eq(6)").remove();
                    $(this).find("th:eq(6)").remove();
                    $(this).find("th:eq(6)").remove();

                    $(this).find("td:eq(6)").remove();
                    $(this).find("td:eq(6)").remove();
                    $(this).find("td:eq(6)").remove();
                });
            }  , 500 ); */
        }
        @elseif(isset($NewUserTypes) && ( end($NewUserTypes)['index'] == 2 ))
        {
            //constituency director
            /* setTimeout( function(){
                $("tr").each(function() {
                    $(this).find("th:eq(7)").remove();
                    $(this).find("th:eq(7)").remove();

                    $(this).find("td:eq(7)").remove();
                    $(this).find("td:eq(7)").remove();
                });
            }  , 500 ); */
        }
        @elseif(isset($NewUserTypes) && $NewUserTypes && end($NewUserTypes)['index'] == 3 ){

        }
        @endif


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
                                                    .text("Select Electral Area"));
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

{{-- @extends('admin.layouts.app')

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
                                <a href="#"  onclick="return confirm('Delete entry?')" class="btn btn-danger btn-small">Delete</a>
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
 --}}
