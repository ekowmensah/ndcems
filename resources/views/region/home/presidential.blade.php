@extends('layouts.app_national')
@section('content')
                <div class="container-fluid">
                        <br>
                        <div class="row">

                        <div class="col-md-12">
                            <div class="panel" >
                                <div class="panel-heading">

                                </div>
                                <div class="panel-body">
                                <div class="col-md-12">
                                    <table class="table" id="table">
                                        <thead>
                                          <tr>
                                            <th>Constituency</th>
                                            <th>Region</th>
                                            <th>Electoral Areas</th>
                                            <th>Polling Station</th>
                                            <th>Registered Voters</th>
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
       ajax: {url:'{!! route("Region.constituencyAajax") !!}',
              data: function (d) {
              d.status = $('#filter-status').val();
              d.terminal = $('#filter-terminal').val();
              d.constituency_id = $('#constituency_id').val();
              d.region_id = $('#region_id').val();

       }},
       "columnDefs": [

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
               var url = "{{ route('Region.constituencyView',':number') }}";
                   url = url.replace(':number', data.id);
                   return `
                                <a style="color:white" class="btn btn-primary btn-xs" href=${url}>View</a>
                            `;
                var confirm = "{{ route('Director.confirmResults',':number') }}";
                confirm = confirm.replace(':number', data.election_result_id);
                   if(data.verify_by_regional==0){
                        return `
                                <a style="color:white" class="btn btn-danger btn-xs" href=${confirm}>Confirm</a>
                                <a style="color:white" class="btn btn-primary btn-xs" href=${url}>View</a>
                            `;
                   }else{
                        return `
                                <a style="color:white" class="btn btn-success btn-xs" href=${confirm}>Verified</a>
                                <a style="color:white" class="btn btn-primary btn-xs" href=${url}>View</a>
                            `;
                   }
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
