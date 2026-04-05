@extends('layouts.app_director')
@section('content')

    <form action="{{route('Director.electionPost')}}" method="POST" id="terminal-form" class="form-horizontal form-label-left input_mask">
        @csrf
        <div >
            <br>
                <div class="container-fluid">

                    <div class="row">

                        <div class="col-md-12">
                            <div class="panel" style="background-color: aliceblue;">
                                <div class="panel-heading">

                                </div>
                                <div class="panel-body">
                                        <div class="col-md-4">
                                          Update Result for Election
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-control" name="election_start_update">
                                                <option value="">Choose</option>
                                                @foreach ($electionStartupDetail as $electionStartup)
                                                    <option value="{{$electionStartup->id}}">{{$electionStartup->election_name}} -- {{$electionStartup->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>
                                        <div class="col-md-4">
                                            Polling Station
                                          </div>
                                          <div class="col-md-6">
                                              <select class="form-control" name="polling_station_id">
                                                  {{-- <option value="">Choose</option> --}}
                                                  @foreach ($pollingStations as $electionStartup)
                                                      <option value="{{$electionStartup->id}}">{{$electionStartup->name}}</option>
                                                  @endforeach
                                              </select>
                                          </div>
                                          <div class="col-md-2">
                                          </div>

                                        <div class="col-md-12">
                                                <br>
                                            </div>
                                        <div class="col-md-4">

                                            </div>
                                            <div class="col-md-6">
                                                    <div class="form-group">
                                                            <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-4">
                                                               <button id="reset" class="btn btn-primary" type="reset">Reset</button>
                                                                <button id="submit" type="submit" class="btn btn-success"> <i id="submitIcon"> </i> Submit</button>
                                                            </div>
                                                        </div>
                                            </div>
                                            <div class="col-md-2">
                                            </div>



                                </div>
                            </div>
                        </div>



                    </div>

                </div>

            </div>
        </form>




@endsection
@section('script')
@endsection
