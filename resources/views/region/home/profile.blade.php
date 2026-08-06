@extends('layouts.app_national')

@section('content')
    @include('national.home.partials.result-analytics-styles')

    <div class="result-shell">
        <div class="result-panel">
            <div class="result-panel__header">
                <div>
                    <h3 class="result-panel__title">Regional Director Profile</h3>
                    <div class="result-panel__sub">Identity and assignment details for the current regional command account.</div>
                </div>
            </div>
            <div class="result-panel__body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="detail-stat">
                            <div class="detail-stat__label">Logged In As</div>
                            <div class="detail-stat__value">{{ $user->user_type_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-stat">
                            <div class="detail-stat__label">Name</div>
                            <div class="detail-stat__value">{{ $user->user_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="detail-stat">
                            <div class="detail-stat__label">Region</div>
                            <div class="detail-stat__value">{{ $user->region_name }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
