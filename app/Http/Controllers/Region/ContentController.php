<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use App\Model\Region;
use App\User;
use App\Services\Region\RegionResultAnalyticsService;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        return view('region.home.index', $this->analytics()->getDashboardData($this->regionId()));
    }

    public function presidentialResultAjax(Request $request)
    {
        return $this->analytics()->getPresidentialChartData($this->regionId(), $this->requestStartupId($request));
    }

    public function Presidential(Request $request)
    {
        return view(
            'region.home.presidential',
            $this->analytics()->getParliamentaryPageData($this->regionId(), $this->requestStartupId($request))
        );
    }

    public function constituencyAajax(Request $request)
    {
        return DataTables::of(
            $this->analytics()->getConstituencyRows(
                $this->regionId(),
                app(\App\Services\National\NationalDashboardService::class)->getConstituencyElectionTypeId(),
                $this->requestStartupId($request)
            )
        )->make(true);
    }

    public function constituencyView(Request $request, $id)
    {
        return view(
            'region.home.presidentialResultView',
            $this->analytics()->getConstituencyDetail($this->regionId(), (int) $id, $this->requestStartupId($request))
        );
    }

    public function PresidentialResult(Request $request)
    {
        return view(
            'region.home.regionalPresidential',
            $this->analytics()->getPresidentialPageData($this->regionId(), $this->requestStartupId($request))
        );
    }

    public function PresidentialAajax(Request $request)
    {
        return DataTables::of(
            $this->analytics()->getConstituencyRows(
                $this->regionId(),
                app(\App\Services\National\NationalDashboardService::class)->getPresidentialElectionTypeId(),
                $this->requestStartupId($request)
            )
        )->make(true);
    }

    public function profile()
    {
        $user = User::select(
            'users.username',
            'users.secret',
            'users.created_at',
            'users.name as user_name',
            'users.id as user_id',
            'user_type.id as user_type_id',
            'user_type.name as user_type_name',
            'region.name as region_name'
        )
            ->where('users.id', Auth::id())
            ->join('user_type', 'user_type.id', '=', 'users.user_type_id')
            ->join('region', 'region.id', '=', 'users.region_id')
            ->first();

        return view('region.home.profile', compact('user'));
    }

    public function regionalResultView(Request $request, $id, $regional_id = null)
    {
        return view(
            'region.home.RegionalResultView',
            $this->analytics()->getRegionDetail($this->regionId(), $this->requestStartupId($request))
        );
    }

    protected function analytics(): RegionResultAnalyticsService
    {
        return app(RegionResultAnalyticsService::class);
    }

    protected function regionId(): int
    {
        return (int) Auth::user()->region_id;
    }

    protected function requestStartupId(Request $request): ?int
    {
        $startupId = $request->query('startup_id', $request->input('startup_id'));

        return is_numeric($startupId) ? (int) $startupId : null;
    }
}
