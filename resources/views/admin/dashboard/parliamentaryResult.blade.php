@extends('admin.layouts.app')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-6" style=" float:  left;">
            <h3> Constituency <small></small></h3>
        </div>
        <div class="col-md-6">
            <br>
                <a href="{{route('SuperAdmin.New.constituency')}}"  style=" float:  right;" class="btn btn-success">Add New Constituency</a>
            </div>
    </div>


</div>
<div class="clearfix"></div>
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
          <div class="x_title">
            <select style="width:25vh" class="form-control filter" name="region_id" id="region_id"  required>
                <option value="all" >Regions (All)<option>
                    @foreach ($regions as $electionType)
                    <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                     @endforeach
            </select>
            <div class="clearfix"></div>
          </div>
          <div class="x_content ">

            <table class="table" id="table">
              <thead>
                <tr>
                  <th>Constituency Name</th>
                  <th>Region Name</th>
                  <th>Total Elactral Area's</th>
                  <th>Total Polling Station</th>
                  <th>Total Voters</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
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
    ajax: {url:'{!! route("SuperAdmin.constituencyAajax") !!}',
           data: function (d) {
           d.status = $('#filter-status').val();
           d.terminal = $('#filter-terminal').val();
           d.constituency_id = $('#constituency_id').val();
           d.region_id = $('#region_id').val();

    }},
    "columnDefs": [

        { "searchable": false, "targets": 2 },
    { "searchable": false, "targets": 3 },
    { "searchable": false, "targets": 4 },
    { "searchable": false, "targets": 5 }
  ],
    columns: [


       { data: 'name', name: 'constituency.name' },
       { data: 'region_name', name: 'region.name' },
       { data: 'total_electral', name: 'total_electral' },
       { data: 'total_polling', name: 'total_polling' },

       { data: 'total_voters', name: 'total_voters' },

       {
        mData:null,
        name:"id",
          "mRender": function (data) {
            var url = "{{ route('SuperAdmin.constituencyView',':number') }}";
                url = url.replace(':number', data.id);
            return `
                   <a style="color:white" class="btn btn-primary btn-xs" href=${url}>View</a>
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
