@extends('layouts.app_director')

@section('content')
<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Result Detail</h4>
        </div>
        <div class="modal-body">
            <iframe width="560" height="315" name="theFrame"  style="display:none" id="theFrame">
            </iframe>
        </div>
        <div class="modal-footer">
          <a href="" class="btn btn-success" id="confirmBtn" >Confirm</a>
        </div>
      </div>

    </div>
  </div>

<div class="container-fluid">
        <br>
        <div class="row">

        <div class="col-md-12">
            <div class="panel" >
                <div class="panel-heading">
                    <div class="col-md-3 col-sm-3 col-xs-12">
                        <select class="form-control filter" name="election_type_id" id="election_type_id" required >
                            <option value="all" >All<option>
                        @foreach ($election as $country)
                                <option value="{{$country->id}}" >{{$country->name}}<option>
                            @endforeach
                        </select>
                </div>
                <br>
                </div>
                <div class="panel-body">
                <div class="col-md-12">
                    <table class="table" id="table">
                        <thead>
                          <tr>
                            <th>Polling Station</th>
                            <th>Election Type</th>
                            <th>Election Name</th>
                            <th>Reg. Voters</th>
                            <th>Valid Votes</th>
                            <th>Total Ballots</th>
                            <th>Rejected Ballot</th>
                            <th>OverVoting</th>
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
       ajax: {url:'{!! route("Director.pollingStationResultAajax") !!}',
              data: function (d) {
              d.election_type_id = $('#election_type_id').val();

       }},
       "columnDefs": [
        { "searchable": false, "targets": 1 },
        { "searchable": false, "targets": 2 },
       { "searchable": false, "targets": 3 },
       { "searchable": false, "targets": 4 },
       { "searchable": false, "targets": 5 },
       { "searchable": false, "targets": 6 },
       { "searchable": false, "targets": 7 },
       { "searchable": false, "targets": 8 }
     ],
       columns: [


          //{ data: 'name', name: 'constituency.name' },
          //{ data: 'region_name', name: 'region.name' },
          //{ data: 'name', name: 'PollingStation.name' },
          {
           mData:null,
           name:"name",
             "mRender": function (data) {
                return `<span title="Polling Agent: ${data.agent_name} (${data.agent_phoneno})" >${data.name} </span>`
            }
            },
          { data: 'election_type_name', name: 'election_type.name' },
          { data: 'election_name', name: 'election_startup_detail.election_name' },
          { data: 'total_voters', name: 'PollingStation.total_voters' },
          { data: 'obtained_votes', name: 'election_result.obtained_votes' },
          { data: 'total_ballot', name: 'election_result.total_ballot' },
          { data: 'total_rejected_ballot', name: 'election_result.total_rejected_ballot' },
          {
           mData:null,
           name:"election_result_id",
             "mRender": function (data) {
                if((data.total_ballot - data.total_voters)>0)
                    return `<span class="fa fa-close" style="color:red;font-size:16px"> ${data.total_ballot-data.total_voters} </span>`
                else
                    return `<span class="fa fa-check-square-o" style="color:green;font-size:16px"> No </span>`
                }

           },
          {
           mData:null,
           name:"election_result_id",
             "mRender": function (data) {
                var url = "{{ route('Director.viewResults',':number') }}";
                    url = url.replace(':number', data.election_result_id);
                var confirm = "{{ route('Director.confirmResults',':number') }}";
                    confirm = confirm.replace(':number', data.election_result_id);
                var del = "{{ route('Director.deleteResults',':number') }}";
                    del = del.replace(':number', data.election_result_id);
                var edit = "{{ route('Director.editResult',[':number',':number1',':userId']) }}";
                    edit = edit.replace(':number', data.election_start_up_id);
                    edit = edit.replace(':number1', data.election_result_id);
                var xlx = "{{ route('Director.resultsXlx',':number') }}";
                    xlx = xlx.replace(':number', data.election_result_id);
                        edit = edit.replace(':userId', data.election_result_user_id || data.id);


                if(data.verify_by_constituency==0){
                    return `
                        <a style="color:white" onclick="confirmation_popup('${url}','${confirm}')" data-toggle="modal" data-target="#myModal"  href=javascript:void(0);><span class="fa fa-spinner fa-pulse" title="Confirm Results" style="color:red;font-size:16px"> <span></a>
                        <a style="color:white"  href=${url}> <span class="fa fa-eye" title="View Results" style="color:#0000CD;font-size:16px"> </span></a>
                        <a style="color:white"  href=${edit}><span class="fa fa-edit" title="Edit/Update Results" style="color:#FF4500;font-size:16px"> </span></a>
                        <a style="color:white" onclick="return confirm('Are you sure?')"  href=${del}><span class="fa fa-trash-o" title="Delete Results" style="color:#FF0000;font-size:16px"> <span></a>

                    `;
                    }else{
                        return `
                            <a style="color:white" onclick="return confirm('Are you sure?')"  href=${confirm}> <span  class="fa fa-check-circle" title="Unconfirm to Edit/Update Results" style="color:#006400;font-size:18px" > <span> </a>
                            <a style="color:white"  href=${url}><span class="fa fa-eye" title="View Results" style="color:#0000CD;font-size:18px"></span></a>
                            <a style="color:white"  href=${edit}><span class="fa fa-edit" title="Edit/Update Results" style="color:#FF4500;font-size:16px"></span></a>
                            <a style="color:white" onclick="return confirm('Are you sure?')"  href=${del}><span class="fa fa-trash-o" title="Delete Results" style="color:#FF0000;font-size:16px"><span></a>
                            <a style="color:white"  href=${xlx}><span class="fa fa-file-excel-o" title="View Results" style="color:green;font-size:18px"></span></a>

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
   function confirmation_popup(url,confirmUrl){
       $('#mymodal').toggle();
       $("#theFrame").show()

        window.open(url, "theFrame");
        $('#confirmBtn').attr("href",confirmUrl)
   }
   $('#theFrame').on('load', function() {
        $("#theFrame").contents().find(".navbar.navbar-default").remove()
    // Handler for "load" called.
    });

   </script>
    <style>
        .current
        {
            background-color: #DDD !important;
        }
    </style>
 <style>

    /* #theFrame {
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        margin: auto;
        width: 500px;
        height: 500px;
        background: white;
        z-index: 999999;
    } */




    </style>
@endsection

