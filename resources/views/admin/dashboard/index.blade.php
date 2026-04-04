@extends('admin.layouts.app')

@section('content')

<div class="row tile_count">
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
          <span class="count_top"><i class="fa fa-user"></i> Regions</span>
          <div class="count" style="font-size:25px">{{number_format($total_region)}}</div>
          <span class="count_bottom"><i class="green"> </i></span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
          <span class="count_top"><i class="fa fa-clock-o"></i>   Constituencies</span>
          <div class="count"style="font-size:25px">{{number_format($total_constituency)}}</div>
          <span class="count_bottom"><i class="green"></i> </span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
          <span class="count_top"><i class="fa fa-user"></i>   Electoral Areas</span>
          <div class="count "style="font-size:25px">{{number_format($total_electoralArea)}}</div>
          <span class="count_bottom"><i class="green"> </i> </span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
            <span class="count_top"><i class="fa fa-user"></i>  Polling Stations</span>
            <div class="count "style="font-size:25px">{{number_format($total_pollingStation)}}</div>
            <span class="count_bottom"><i class="green"> </i> </span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top"><i class="fa fa-user"></i> Total Voters</span>
                <div class="count "style="font-size:25px">{{number_format($total_voters)}}</div>
                <span class="count_bottom"><i class="green"> </i> </span>
            </div>

        {{-- <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
          <span class="count_top"><i class="fa fa-user"></i> Total Presidential Candidates </span>
          <div class="count">4,567</div>
          <span class="count_bottom"><i class="red"> </i> </span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
          <span class="count_top"><i class="fa fa-user"></i> Total Parliamentary Candidates</span>
          <div class="count">2,315</div>
          <span class="count_bottom"><i class="green"> </i> </span>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
          <span class="count_top"><i class="fa fa-user"></i> Total District Assembly Candidates </span>
          <div class="count">7,325</div>
          <span class="count_bottom"><i class="green"> </i> </span>
        </div> --}}
        @foreach ($userTypeDetail as $detail)
        <div class="col-md-2 col-sm-4 col-xs-6 tile_stats_count">
                <span class="count_top"><i class="fa fa-user"></i>  {{$detail->name}}s</span>
                <div class="count"style="font-size:25px">{{$detail->user_type__count}}</div>
                <span class="count_bottom"><i class="green"> </i> </span>
              </div>
        @endforeach
      </div>


@endsection
@section("script")



        @endsection
