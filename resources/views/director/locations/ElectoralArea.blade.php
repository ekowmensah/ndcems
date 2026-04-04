@extends('layouts.app_director')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Manage Electoral Area <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('Director.New.ElectoralArea')}}"  style=" float:  right;" class="btn btn-success">Add New Electoral Area</a>
            </div>
    </div>



<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">



            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table" id="table">
              <thead>
                <tr>
                    <th>ElectoralArea Name</th>

                    <th>Constituency Name</th>
                  <th>Region Name</th>
                  <th>Country Name</th>
                  <th>Total Polling Stations</th>
                  <th>Total Voter</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                 {{--  @foreach ($regions as $country)
                        <tr>

                            <td>{{$country->name}}</td>
                            <td>{{$country->constituency_name}}</td>
                            <td>{{$country->region_name}}</td>
                            <td>{{$country->country_name}}</td>
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
        })
        </script>
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
               ajax: {url:'{!! route("Director.electralAajax") !!}',
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



               }},
               "columnDefs": [
                    { "searchable": false, "targets": 4 },
                    { "searchable": false, "targets": 5 }
                ],
               columns: [


                  { data: 'name', name: 'ElectoralArea.name' },
                  { data: 'constituency_name', name: 'constituency.name' },
                  { data: 'region_name', name: 'region.name' },
                  { data: 'country_name', name: 'countries.name' },
                  { data: 'total_polling', name: 'total_polling' },
                  { data: 'total_voters', name: 'total_voters' },

                  {
                   mData:null,
                   name:"id",
                     "mRender": function (data) {

           var del = "{{ route('Director.ElectoralAreaDelete',':number') }}"
                           del = del.replace(':number', data.id);

                       var url = "{{ route('Director.ElectoralAreaEdit',':number') }}";
                           url = url.replace(':number', data.id);

                       return `
                       <a style="color:white" class="btn btn-primary btn-xs" href=${url}>Edit</a>
                              <a onclick="return confirm('Delete entry?')" style="color:white" class="btn btn-danger btn-xs" href=${del}>Delete</a>
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
