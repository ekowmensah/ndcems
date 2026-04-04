@extends('layouts.app_national_director')
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
                                            <th>Party</th>
                                            <th>Region Name</th>
                                            <th>Total Ballot</th>
                                            <th>total rejected Ballot</th>
                                            <th>Obtained Vote</th>
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
       ajax: {url:'{!! route("National.PresidentialAajax") !!}',
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


          { data: 'party_initial', name: 'political_party.party_initial' },
          { data: 'region_name', name: 'region.name' },
          { data: 'total_ballot', name: 'election_result.total_ballot' },
          { data: 'total_rejected_ballot', name: 'election_result.total_rejected_ballot' },

          { data: 'election_result', name: 'election_result' },

          {
           mData:null,
           name:"id",
             "mRender": function (data) {
               var url = "{{ route('Region.constituencyView',':number') }}";
                   url = url.replace(':number', data.id);
               return `
                      <a style="color:white" class="btn btn-primary btn-xs" >View</a>
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
