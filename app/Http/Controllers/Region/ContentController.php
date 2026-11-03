<?php

namespace App\Http\Controllers\Region;

use App\Http\Controllers\Controller;
use App\Model\Region;
use App\User;
use App\Services\Region\RegionResultAnalyticsService;
use App\Services\Results\ResultOversightService;
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

    public function resultReview(Request $request, ResultOversightService $oversight)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'anomaly' => ['nullable', 'in:over_voting,arithmetic,party_mismatch,missing_pink_sheet,escalated,stale_pending,reopened'],
            'election_type_id' => ['nullable', 'integer'],
            'startup_id' => ['nullable', 'integer'],
        ]);

        return view('oversight.results', $oversight->getPageData($filters, $this->regionId()) + [
            'layout' => 'layouts.app_national',
            'routePrefix' => 'Region',
            'scopeLabel' => 'Regional',
            'regionScope' => true,
        ]);
    }

    public function resultReviewDetail($id, ResultOversightService $oversight)
    {
        return view('oversight.detail', $oversight->getDetail((int) $id, Auth::user(), $this->regionId()) + [
            'layout' => 'layouts.app_national',
            'routePrefix' => 'Region',
            'scopeLabel' => 'Regional',
        ]);
    }

    public function reviewPinkSheet($id, ResultOversightService $oversight)
    {
        return response()->file($oversight->pinkSheetPath((int) $id, $this->regionId()), [
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function storeEscalation(Request $request, $id, ResultOversightService $oversight)
    {
        $data = $request->validate([
            'category' => ['required', 'in:data_quality,missing_evidence,reporting_delay,dispute,other'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'note' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        $oversight->createEscalation((int) $id, Auth::user(), $data, 'Regional Director', $this->regionId());
        return redirect()->back()->with('message', 'Escalation recorded for follow-up.');
    }

    public function resolveEscalation(Request $request, $id, ResultOversightService $oversight)
    {
        $data = $request->validate(['resolution_note' => ['required', 'string', 'min:10', 'max:2000']]);
        $oversight->resolveEscalation((int) $id, Auth::user(), $data['resolution_note'], $this->regionId());
        return redirect()->back()->with('message', 'Escalation marked as resolved.');
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
