@extends('layouts.app_national')
@section('content')



            <div >
                <div class="container-fluid">
                    <br>

                    <div class="row">

                        <div class="col-md-12">
                            <div class="panel" style="background-color: aliceblue;">
                                <div class="panel-heading">
                                    <h1 class="panel-title"><strong>My Regional Area Detail</strong></h1>
                                </div>
                                <div class="panel-body">

                                        <div class="col-md-4">
                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>

                                        <div class="col-md-4">
                                                <strong> Loged In as : </strong> {{$user->user_type_name}}
                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-12">
                                            <br>
                                        </div>
                                        <div class="col-md-4">
                                                <strong> Region : </strong> {{$user->region_name}}
                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                        <div class="col-md-4">

                                        </div>
                                </div>
                            </div>
                        </div>


                    </div>
                    <!-- end new -->
                    <!-- new 1 -->

                </div>
                <!-- end new 1 -->
                <!-- new 1 -->

            </div>





@endsection
@section('script')
@endsection

