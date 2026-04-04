@extends('layouts.app_director')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-3" style=" float:  left;">
            @if($type)

            <h3>{{$type->name}} Candidates <small></small></h3>
            @else
            <h3>Candidates <small></small></h3>
            @endif
        </div>
        <div class="col-md-9">
            <br>
            @if($type)
                    <a href="{{route('Director.candidateRegister')}}"  style=" float:  right;" class="btn btn-success">Add {{$type->name}} Candidate</a>

            @else
                @foreach ($_electionTypes as $electionType)
                    <a href="{{route('Director.candidateRegister',$electionType->id)}}"  style=" float:  right;" class="btn btn-success">Add {{$electionType->name}} Candidate</a>
                @endforeach
            @endif
            </div>
    </div>


<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">



            @if(!$type)
            <select style="width:25vh" class="form-control filter" name="election_type_id" id="election_type_id"  required>
                <option value="all" >Type (All)<option>
                    @foreach ($_electionTypes as $electionType)
                    <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                     @endforeach
            </select>
            @endif

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">



               <br>
            <table class="table" id="table">
              <thead>
                <tr>
                    <th>Logo</th>
                    {{-- <th>Card ID</th --}}
                  <th>Position (Ordering)</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  {{-- <th>Date Of Birth</th> --}}
                  <th>Election </th>
                  <th>Political Party </th>
                  <th></th>

                </tr>
              </thead>
             </table>

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
    ajax: {url:'{!! route("Director.candidateAjax") !!}',
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
            var matches = "/candidate_logo/"+data.photo;
                match = matches.replace('&quot;', '');
                match = match.replace('&quot;', '');
           return `
                <img src="${matches}" class="img-thumbnail" alt="${data.name}" width="50" height="50">
               `;
           }
        },
       //{ data: 'id_no', name: 'id_no' },
       { data: 'ordering_position', name: 'candidates.ordering_position' },
       { data: 'first_name', name: 'first_name' },
       { data: 'last_name', name: 'last_name' },
       { data: 'election_type_name', name: 'election_type_name' },
       { data: 'political_party_name', name: 'political_party_name' },
        {
        mData:null,
        name:"trans_id",
          "mRender": function (data) {

var del = "{{ route('Director.candidateDelete',':number') }}"
                del = del.replace(':number', data.id);

            var url = "{{ route('Director.candidateEdit',':number') }}";
                url = url.replace(':number', data.id);
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
                        url: '{{route("Director.getConstituency")}}',
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
                                url: '{{route("Director.getElectral")}}',
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
                })
                </script>
        @endsection
