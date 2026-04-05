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
<div class="modal fade" id="pinkSheetUploadModal" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="director-pink-sheet-upload-form" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Upload Pink Sheet</h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Select image</label>
                    <input type="file" name="pink_sheet" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                    <small class="text-muted">Allowed: JPG, PNG, WEBP. Max size: 5MB.</small>
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary">Upload</button>
            </div>
        </form>
      </div>
    </div>
  </div>
<div class="modal fade" id="pinkSheetPreviewModal" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Pink Sheet Preview</h4>
        </div>
        <div class="modal-body text-center">
            <p><a href="#" id="directorPinkSheetOpenLink" target="_blank" rel="noopener">Open Full Image</a></p>
            <img id="directorPinkSheetPreviewImage" src="" alt="Pink Sheet" style="max-width:100%;max-height:75vh;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;">
        </div>
        <div class="modal-footer">
            <span id="directorPinkSheetActionHint" style="float:left;color:#666;display:none;">Confirmed result: print/download enabled.</span>
            <a href="#" id="directorPinkSheetDownloadBtn" class="btn btn-success" target="_blank" rel="noopener" style="display:none;">
                <span class="fa fa-download"></span> Download
            </a>
            <button type="button" id="directorPinkSheetPrintBtn" class="btn btn-primary" style="display:none;">
                <span class="fa fa-print"></span> Print
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
                            <th>Pink Sheet</th>
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
       { "searchable": false, "targets": 8 },
       { "searchable": false, "targets": 9 }
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
           name:"pink_sheet_path",
               "mRender": function (data) {
                 if(data.pink_sheet_path){
                     var pinkSheetUrl = "{{ route('Director.ViewPinkSheet', ':number') }}";
                     pinkSheetUrl = pinkSheetUrl.replace(':number', data.election_result_id);
                    var pinkSheetDownloadUrl = "{{ route('Director.DownloadPinkSheet', ':number') }}";
                    pinkSheetDownloadUrl = pinkSheetDownloadUrl.replace(':number', data.election_result_id);
                    var reuploadButton = '';
                    if(data.verify_by_constituency==0){
                        reuploadButton = `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="openDirectorPinkSheetModal(${data.election_result_id})">Reupload</a>`;
                    }else{
                        reuploadButton = `<span style="color:#2e7d32;font-weight:600;">Locked</span>`;
                    }
                    return `
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <img src="${pinkSheetUrl}" alt="Pink Sheet" style="width:46px;height:46px;object-fit:cover;border:1px solid #d9d9d9;border-radius:4px;">
                            <a href="javascript:void(0)" style="color:#1565c0;font-weight:600;" onclick="openDirectorPinkSheetPreview('${pinkSheetUrl}', ${data.verify_by_constituency}, '${pinkSheetDownloadUrl}')">View</a>
                            ${reuploadButton}
                        </div>
                    `;
                 }
                if(data.verify_by_constituency==0){
                    return `<a href="javascript:void(0)" class="btn btn-xs btn-primary" onclick="openDirectorPinkSheetModal(${data.election_result_id})">Upload</a>`;
                }
                return `<span style="color:#999;">Not uploaded (locked)</span>`;
              }
          },
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
                var edit = "{{ route('Director.editResult',[':number',':number1']) }}";
                    edit = edit.replace(':number', data.election_start_up_id);
                    edit = edit.replace(':number1', data.election_result_id);
                var xlx = "{{ route('Director.resultsXlx',':number') }}";
                    xlx = xlx.replace(':number', data.election_result_id);
                if(data.election_result_user_id){
                    edit = edit + "/" + data.election_result_user_id;
                }


                if(data.verify_by_constituency==0){
                    return `
                        <a style="color:white" onclick="confirmation_popup('${url}','${confirm}')" data-toggle="modal" data-target="#myModal"  href=javascript:void(0);><span class="fa fa-spinner fa-pulse" title="Confirm Results" style="color:red;font-size:16px"> <span></a>
                        <a style="color:white"  href=${url}> <span class="fa fa-eye" title="View Results" style="color:#0000CD;font-size:16px"> </span></a>
                        <a style="color:white"  href=${edit}><span class="fa fa-edit" title="Edit/Update Results" style="color:#FF4500;font-size:16px"> </span></a>
                        <a style="color:white" onclick="return confirm('Are you sure?')"  href=${del}><span class="fa fa-trash-o" title="Delete Results" style="color:#FF0000;font-size:16px"> <span></a>

                    `;
                    }else{
                        return `
                            <a style="color:white" onclick="return confirm('Are you sure?')"  href=${confirm}> <span  class="fa fa-check-circle" title="Unconfirm Result" style="color:#006400;font-size:18px" > <span> </a>
                            <a style="color:white"  href=${url}><span class="fa fa-eye" title="View Results" style="color:#0000CD;font-size:18px"></span></a>
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
   function openDirectorPinkSheetModal(electionResultId){
        var uploadUrl = "{{ route('Director.UploadPinkSheet', ':number') }}";
        uploadUrl = uploadUrl.replace(':number', electionResultId);
        $('#director-pink-sheet-upload-form').attr('action', uploadUrl);
        $('#pinkSheetUploadModal').modal('show');
   }
   function openDirectorPinkSheetPreview(url, isConfirmed, downloadUrl){
        $('#directorPinkSheetPreviewImage').attr('src', url);
        $('#directorPinkSheetOpenLink').attr('href', url);
        if(parseInt(isConfirmed, 10) === 1){
            $('#directorPinkSheetActionHint').show();
            $('#directorPinkSheetDownloadBtn').attr('href', downloadUrl).show();
            $('#directorPinkSheetPrintBtn').show().off('click').on('click', function () {
                var printWindow = window.open('', '_blank');
                printWindow.document.write('<html><head><title>Pink Sheet</title></head><body style="margin:0;text-align:center;"><img src="' + url + '" style="max-width:100%;height:auto;" onload="window.print();window.close();"></body></html>');
                printWindow.document.close();
            });
        }else{
            $('#directorPinkSheetActionHint').hide();
            $('#directorPinkSheetDownloadBtn').hide().attr('href', '#');
            $('#directorPinkSheetPrintBtn').hide().off('click');
        }
        $('#pinkSheetPreviewModal').modal('show');
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
