@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3>Political Parties <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('SuperAdmin.New.politicalParty')}}"  style=" float:  right;" class="btn btn-success">Add Political Party</a>
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-10 col-sm-10 col-xs-10 col-md-offset-1">
        <div class="x_panel">
          <div class="x_title">
            <h2> Political Parties </h2>

            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table" id="table">
              <thead>
                <tr>
                    <th>Logo</th>
                    <th>Party ID</th>
                  <th>Name</th>
                  <th>Party Inital</th>
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

    var data_table = $('#table').DataTable({
        //responsive: true,
    processing: true,
    serverSide: true,
    //"iDisplayLength": 100,
    "pageLength": 10,
    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
   stateSave: true,
    "order": [[ 1, "asc" ]],
    ajax: {url:'{!! route("SuperAdmin.politicalPartyAjax") !!}',
           data: function (d) {
           d.status = $('#filter-status').val();
           d.terminal = $('#filter-terminal').val();
           d.client = $('#filter-client').val();
           d.order_id = $('#order_id').val();

    }},
    columns: [

       {

        mData:null,
        name:"logo",
          "mRender": function (data) {
            var matches = "/party_logo/"+data.logo;
                match = matches.replace('&quot;', '');
                match = match.replace('&quot;', '');
           return `
                <img src="${matches}" class="img-thumbnail" alt="${data.party_initial}" width="50" height="50">
               `;
           }
        },
       { data: 'party_id', name: 'party_id' },
       { data: 'name', name: 'name' },
       { data: 'party_initial', name: 'party_initial' },
        {
        mData:null,
        name:"trans_id",
          "mRender": function (data) {

var del = "{{ route('SuperAdmin.delete.politicalParty',':number') }}"
                del = del.replace(':number', data.id);

            var url = "{{ route('SuperAdmin.Edit.politicalParty',':number') }}";
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
