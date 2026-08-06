@extends('layouts.app_national_director')
@section('page_title', 'Profile')
@section('page_description', 'Your national workspace identity, geographic assignment, and operating role at a glance.')
@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">National User Profile</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> {{ $user->user_name }}</p>
                            <p><strong>Username:</strong> {{ $user->username }}</p>
                            <p><strong>Role:</strong> {{ $user->user_type_name }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Region:</strong> {{ $user->region_name ?? 'Not assigned' }}</p>
                            <p><strong>Constituency:</strong> {{ $user->constituency_name ?? 'Not assigned' }}</p>
                            <p><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
@endsection
