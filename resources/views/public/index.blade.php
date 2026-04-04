<!DOCTYPE html>
<html lang="en">
<head>
  <title>National Democratic Congress - Election Results Transfer System</title>
  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css?v=4.001">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
  <link rel="icon" href="https://www.ndcresults.live/img/logo.png" type="image/png" sizes="16x16">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
          .progress-bar{
            line-height: 39px;
            font-size: 19px;
          }

  </style>
</head>
<body style="background-image: linear-gradient(black, red, white, green)"; background-repeat:no-repeat; background-attachment: fixed;">

<div id="custom-bootstrap-menu" class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header"><a class="navbar-brand" href="{{url('/')}}" style="padding:0px!important;"><img style="width:100px;height: 54px;" src='{{ asset($config['logo']) }}' class="img-responsive" /></a>
           <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-menubuilder">Select <strong style="color:green">LIVE!</strong> Results
            </button> 
        </div>
        <div class="collapse navbar-collapse navbar-menubuilder">
            <ul class="nav navbar-nav navbar-right">

                <li class=" {{ (Request::is('parliament') ? 'active' : '') }}"><a class="card-header card-header-primary text-center card-title" href="{{route('parliament')}}">Parliamentary Results</a></li>
      <li class=" {{ (Request::is('president') ? 'active' : '') }}"><a class="card-header card-header-primary text-center card-title" href="{{route('president')}}">Presidential Results</a></li>
      <li> <a class="card-header card-header-primary text-center card-title" href="{{ route('login') }}">{{ __('Login') }}</a></li>
            </ul>
        </div>
    </div>
</div>

<br><br><br>

<div class="sticky-top" style="
       color: white;
       font-size: 1.5em;
       padding: 1rem;
       text-align: center;
       text-transform: uppercase;position:sticky;">

    <span>
        <strong>LIVE RESULTS UPDATES</strong>
        <p>
         <span style="background-color:#fff;color:red;text-transform: capitalize;font-size:14px">(Results are updated in real time as and when Agents capture results from the Polling Station)</span>
        </p>
    </span>

       <div>
       
        <div  style="padding: 5px; height: auto; text-align:center; position:sticky;">
        <h3 >
        <button type="button" class="btn btn-success btn-lg"><strong id="polling_count">{{$polling_count}}</strong></button> <strong>/</strong> 
        <button type="button" class="btn btn-danger btn-lg"><strong id="all_polling_count">{{$all_polling_count}}</strong></button> 
        <button type="button" class="btn btn-warning">Confirmed Results</button></button> 
        <button type="button" class="btn btn-primary"> VIEW SUMMARY</button></h3>
        </div>
</div>

       </div>



  <div class="container" style="padding: 5px; height: auto;text-align:center;">
        <select id="election_start_up_id" style="width:25vh;display: inline-block;" class="filter form-control" >
           <option>Select</option>
            @foreach ($electionStartupDetail as $electionStartup)
                <option value="{{$electionStartup->id}}" {{$electionStartup->id==$id ? "selected":""}}>{{$electionStartup->election_name}} -- {{$electionStartup->name}}</option>
            @endforeach
        </select>

        <select style="width:25vh;display: inline-block;" class="form-control filter" name="region_id" id="region_id"  required>
            <option value="all" >Regions (All)<option>
                @foreach ($regions as $electionType)
                <option value="{{$electionType->id}}"  >{{$electionType->name}}<option>
                 @endforeach
        </select>
        <select style="width:25vh;display: inline-block;" class="form-control filter" name="constituency_id" id="constituency_id"  required>


            </select>
            <select style="width:25vh;display: inline-block;" class="form-control filter" name="electoralarea_id" id="electoralarea_id"  required>


                </select>
                <select style="width:25vh;display: inline-block; " class="form-control filter" name="polling_station_id" id="polling_station_id"  required>


                    </select>
    </div>
<div id="result_container">

   @foreach ($allElectionResults as $key => $election)
<div class="container">

    <div class="row" style="margin:5px;">
        <div class="col-sm-12 border" style="background-color: #327a81; border: 0.5px solid grey; text-align:center;">

        <div class="col-md-1 center-block" style="text-align:center;"><a href="{{ asset('candidate_logo/'.$election->photo) }}" data-toggle="modal" data-target="{{ asset('candidate_logo/'.$election->photo) }}"><img src="{{ asset('candidate_logo/'.$election->photo) }}"
                alt="{{$election->first_name}} {{$election->last_name}}" title="{{$election->first_name}} {{$election->last_name}}"  width="90" class="img-circle" height="90" /></a></div>
        <div class="col-md-3" style="padding:20px 0 10px 0"><h4> <strong>{{$election->first_name}} {{$election->last_name}}</strong> </h4></div>
        <div class="col-md-4"><div style="padding:20px 0 10px 0">
             <p><strong>{{$election->political_party_name}}</strong></p>
             <p><strong>({{$election->party_initial}})</strong></p></div>
        </div>
        <div style="padding:20px 0 10px 0">
        <div class="col-md-2 center-block" title="Percentage of Votes Obtained" style="background-color:{{$key<=2 ? $colors[$key] : '#C24641'}}; color:#ffffff;"><h3><strong>{{round($election->percentage,2)}}%</strong></h3></div>
        <div class="col-md-2 center-block" title="Valid Votes Obtained by Candidate" style="background-color:#FFFF66;"><h3><strong>{{number_format($election->party_election_result_obtained_vote)}}</strong></h3></div>
    </div></div></div>
</div>
   @endforeach
</div>
{{-- <script>$(function(){
    $('body').fadeIn(15000);
    setTimeout(function(){
        $('body').fadeOut(10000, function(){
            location.reload(true);
        });
    }, 30000);
});</script> --}}
<script>

   var colors = new Array('#0000FF','#98FB98','red');
   function getData(){
    var constituency_id;
        var region_id;
        var electoralarea_id;
        var polling_station_id;
            if(election_type_id!==""){
                election_type_id = election_type_id
            }
            else
            {
                election_type_id = "all"
            }

            if($('#constituency_id').val()){
                constituency_id = $('#constituency_id').val();
              }else {
                constituency_id ="all"
              }

            if($('#region_id').val()){
                region_id = $('#region_id').val();
              }else{
                region_id = "all"
              }
            if($('#electoralarea_id').val()){
                electoralarea_id = $('#electoralarea_id').val();
              }else{
                electoralarea_id = "all"
              }
            if($('#polling_station_id').val()){
                polling_station_id = $('#polling_station_id').val();
              }else{
                polling_station_id = "all"
              }
            if($('#election_start_up_id').val()){
                election_start_up_id = $('#election_start_up_id').val();
              }else{
                election_start_up_id = "all"
              }


        var _token = $('input[name="_token"]').val();

        var election_type_id = "{{ $id}}"
        var newElectionType = "{{ $newElectionType}}"


        $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
        });


           $.ajax({
               url: "{{route('ajaxResult')}}",
               type: 'POST',
               data: {
                   _token : _token,
                   election_type_id:election_type_id,
                   newElectionType:newElectionType,
                   election_start_up_id:election_start_up_id,
                   polling_station_id:polling_station_id,
                   electoralarea_id:electoralarea_id,
                   region_id:region_id,
                   constituency_id:constituency_id

               },
               success: function (data) {
                $('#result_container').empty()

                /* for(var i=0; i<Object.keys(data).length;i++){
                    console.log(data[i])
                } */
                jQuery.each(data, function(index, item) {
                    //console.log(data)
                    $('#result_container').append(`

                    <div class="container">

                    <div class="row" style="margin:5px;">
                        <div class="col-sm-12 border" style="background-color: #327a81; border: 0.5px solid grey; text-align:center;">

                        <div class="col-md-1 center-block" style="text-align:center;"><a href="{{ asset('candidate_logo') }}/${item.photo}" data-toggle="modal" data-target="{{ asset('candidate_logo') }}/${item.photo}"><img src="{{ asset('candidate_logo') }}/${item.photo}"
                                alt="${item.first_name} ${item.last_name}" title="${item.first_name} ${item.last_name}"  width="90" class="img-circle" height="90" /></a></div>
                        <div class="col-md-3" style="padding:20px 0 10px 0"><h4> <strong>${item.first_name} ${item.last_name}</strong> </h4></div>
                        <div class="col-md-4"><div style="padding:20px 0 10px 0">
                            <p><strong>${item.political_party_name}</strong></p>
                            <p><strong>(${item.party_initial})</strong></p></div>
                        </div>
                        <div style="padding:20px 0 10px 0">
                        <div class="col-md-2 center-block" title="Percentage of Votes Obtained" style="background-color:${index<=2 ? colors[index] : '#C24641'}; color:#ffffff;"><h3><strong>${Math.round(item.percentage*100)/100}%</strong></h3></div>
                        <div class="col-md-2 center-block" title="Valid Votes Obtained by Candidate" style="background-color:#FFFF66;"><h3><strong>${ item.party_election_result_obtained_vote.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")}  </strong></h3></div>
                    </div></div></div>
                    </div>



                    `)
                });
               }
           });

           $.ajax({
               url: "{{route('ajaxCountResult')}}",
               type: 'POST',
               data: {
                   _token : _token,
                   election_type_id:election_type_id,
                   newElectionType:newElectionType,
                   election_start_up_id:election_start_up_id,
                   polling_station_id:polling_station_id,
                   electoralarea_id:electoralarea_id,
                   region_id:region_id,
                   constituency_id:constituency_id

               },
               success: function (data) {

                $('#all_polling_count').empty()
                $('#polling_count').empty()
                $('#all_polling_count').append(data.all_polling_count)
                $('#polling_count').append(data.polling_count)

               }
           });
    }
    setInterval(function(){
        getData()

    }, 3000);

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
                        url: '{{route("getConstituency")}}',
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
                                url: '{{route("getElectral")}}',
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
                        $("#electoralarea_id").on('change', '', function (e) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                $("#polling_station_id").empty();
                var electoralarea_id = $("#electoralarea_id").val()
                // $("#username_panel").append(`<span class="fa fa-spinner"><span>` )
                        var _token = $('input[name="_token"]').val();
                    $.ajax({
                            type: "POST",
                            url: '{{route("getPollingStation")}}',
                            data: {electoralarea_id:electoralarea_id,_token:_token},
                            //dataType: "JSON",
                            success: function (result) {
                                $('#polling_station_id')
                                        .append($("<option></option>")
                                                    .attr("value","all")
                                                    .text("Select Polling station"));
                                $.each(result, function(key, value) {
                                    $('#polling_station_id')
                                        .append($("<option></option>")
                                                    .attr("value",value.id)
                                                    .text(value.name));
                                });
                            }
                        });
                    });

                    $("#election_start_up_id").on('change', '', function (e) {
                        $('#region_id').val($("#region_id option:first").val());
                        $('#constituency_id').val($("#constituency_id option:first").val());
                        $('#electoralarea_id').val($("#electoralarea_id option:first").val());
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
                    $("#region_id").on('change', '', function (e) {
                        $('#constituency_id').val($("#constituency_id option:first").val());
                        $('#electoralarea_id').val($("#electoralarea_id option:first").val());
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
                    $("#constituency_id").on('change', '', function (e) {
                        $('#electoralarea_id').val($("#electoralarea_id option:first").val());
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });
                    $("#electoralarea_id").on('change', '', function (e) {
                        $('#polling_station_id').val($("#polling_station_id option:first").val());
                    });

                    $(".filter").on('change', '', function (e) {
                        getData()
                    });
                })
</script>
<br>
<!-- Footer -->
<footer class="page-footer font-small blue fixed-bottom">

  <!-- Copyright -->
  <div class="footer-copyright text-center py-3"> 2018 Copyright:
    <a href="https://facebook.com/ekowmenzah" target="_blank"> Ekow - AOB NDC</a>
 <!--   <a href="https://freelancer.com/u/imrj" target="_blank"> Ekow - AOB NDC</a>-->
  </div>
  <!-- Copyright -->

</footer>
<!-- Footer -->


</body>
</html>
