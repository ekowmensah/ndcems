@extends('layouts.app_national_director')
@section('content')
                <div class="container-fluid">

                        <div class="col-md-12">
                            <div class="panel" >
                                <div class="panel-heading">
                                        <br>
                                        <div class="row">
                                                @if($type)
                                                <a href="{{route('National.candidateRegister',$type->id)}}"  style=" float:  right;" class="btn btn-success">Add {{$type->name}} Candidate</a>

                                        @else
                                            @foreach ($_electionTypes as $electionType)
                                                <a href="{{route('National.candidateRegister',$electionType->id)}}"  style=" float:  right;" class="btn btn-success">Add {{$electionType->name}} Candidate</a>
                                            @endforeach
                                        @endif
                                        <br><br>
                                        <select style="width:25vh; float:right" class="form-control filter" name="electoralarea_id" id="electoralarea_id"  required>


                                            </select>
                                    <select style="width:25vh; float:right" class="form-control filter" name="constituency_id" id="constituency_id"  required>


                                            </select>
                                    <select style="width:25vh ;float:right" class="form-control filter" name="region_id" id="region_id"  required>
                                        <option value="all" >Regions (All)<option>
                                            @foreach ($regions as $electionType)
                                            <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                                             @endforeach
                                    </select>
                                    @if(!$type)
                                    <select style="width:25vh;float:right" class="form-control filter" name="election_type_id" id="election_type_id"  required>
                                        <option value="all" >Type (All)<option>
                                            @foreach ($_electionTypes as $electionType)
                                            <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                                             @endforeach
                                    </select>
                                    @endif
                                </div>
                                <div class="panel-body">
                                <div class="col-md-12">
                                        <table class="table" id="table">
                                                <thead>
                                                  <tr>
                                                      <th>Logo</th>
                                                      {{-- <th>Card ID</th --}}
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    {{-- <th>Date Of Birth</th> --}}
                                                    <th>For Election </th>
                                                    <th>Political Party </th>
                                                    {{-- <th></th> --}}

                                                  </tr>
                                                </thead>
                                               </table>

                                </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@section("script")
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
       ajax: {url:'{!! route("National.candidateAjax") !!}',
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

                 if($('#election_type_id').val()){
                   d.election_type_id = $('#election_type_id').val();
                 }else{
                   d.election_type_id = "all"
                 }


                 var id = "{{ $id}}"
               if(id!==""){
                   d.id = id
               }
               else
               {
                   d.id = "none"
               }


       }},
       columns: [

          {

           mData:null,
           name:"photo",
             "mRender": function (data) {
               var matches = "{{ asset('candidate_logo') }}/"+data.photo;
                   match = matches.replace('&quot;', '');
                   match = match.replace('&quot;', '');
              return `
                   <img src="${matches}" class="img-thumbnail" alt="${data.name}" width="50" height="50">
                  `;
              }
           },
          //{ data: 'id_no', name: 'id_no' },
          { data: 'first_name', name: 'first_name' },
          { data: 'last_name', name: 'last_name' },
          { data: 'election_type_name', name: 'election_type_name' },
          { data: 'political_party_name', name: 'political_party_name' }
           /* {
           mData:null,
           name:"trans_id",
             "mRender": function (data) {

                var del = "{{ route('SuperAdmin.candidateDelete',':number') }}"
                   del = del.replace(':number', data.id);

               var url = "{{ route('SuperAdmin.candidateEdit',':number') }}";
                   url = url.replace(':number', data.id);
               return `
                      <a style="color:white" class="btn btn-primary btn-xs" href=${url}>Edit</a>
                      <a style="color:white" class="btn btn-danger btn-xs" onclick="return confirm('Delete entry?')" href=${del}>Delete</a>
                  `;
              }
           } */
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
                           url: '{{route("National.getConstituency")}}',
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
                                   url: '{{route("National.getElectral")}}',
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
                   })
                   </script>
        @endsection
