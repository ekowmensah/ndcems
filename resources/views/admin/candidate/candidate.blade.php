@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-3" style=" float:  left;">
            <h3>Candidate <small></small></h3>
        </div>
        <div class="col-md-9">
            <br>
            @foreach ($electionTypes as $electionType)
                <a href="{{route('SuperAdmin.candidateRegister',$electionType->id)}}"  style=" float:  right;" class="btn btn-success"> Add {{$electionType->name}}</a>
            @endforeach
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
                <select style="width:25vh" class="form-control filter" name="election_id" id="election_id"  required>
                        <option value="all" >Election Type (All)<option>
                            @foreach ($electionTypes as $electionType)
                            <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                             @endforeach
        </select>
        <select style="width:25vh" class="form-control filter" name="constituency_id" id="constituency_id"  required>
            <option value="all" >Constituency (All)<option>
                @foreach ($Constituencies as $electionType)
                <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                 @endforeach
        </select>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">



               <br>
            <table class="table" id="table">
              <thead>
                <tr>
                    <th>Logo</th>
                    <th>Card ID</th>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Date Of Birth</th>
                  <th>For Election </th>
                  <th></th>

                </tr>
              </thead>
             </table>

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
    ajax: {url:'{!! route("SuperAdmin.candidateAjax") !!}',
           data: function (d) {
           d.status = $('#filter-status').val();
           d.terminal = $('#filter-terminal').val();
           d.constituency_id = $('#constituency_id').val();
           d.election_id = $('#election_id').val();

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
       { data: 'id_no', name: 'id_no' },
       { data: 'first_name', name: 'first_name' },
       { data: 'last_name', name: 'last_name' },
       { data: 'election_type_name', name: 'election_type_name' },

        {
        mData:null,
        name:"trans_id",
          "mRender": function (data) {

var del = "{{ route('SuperAdmin.candidateDelete',':number') }}"
                del = del.replace(':number', data.id);

            var url = "{{ route('SuperAdmin.candidateEdit',':number') }}";
                url = url.replace(':number', data.id);
            return `
                   <a style="color:white" class="btn btn-primary btn-xs" href=${url}>Edit</a>
                   <a style="color:white" class="btn btn-danger btn-xs" href=${del}>Delete</a>
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
