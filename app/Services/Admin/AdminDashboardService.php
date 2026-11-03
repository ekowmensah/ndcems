<?php

namespace App\Services\Admin;

use App\Model\Constituency;
use App\Model\ElectionStartupDetail;
use App\Model\ElectoralArea;
use App\Model\PollingStation;
use App\Model\Region;
use App\Model\UserType;
use App\Services\National\NationalResultAnalyticsService;
use App\Services\National\NationalDashboardService;
use App\SuperAdmin;
use App\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    public function __construct(
        protected NationalResultAnalyticsService $resultAnalytics,
        protected NationalDashboardService $nationalDashboard
    ) {
    }

    public function getDashboardData(): array
    {
        $presidential = $this->resultAnalytics->getPresidentialPageData();
        $parliamentary = $this->resultAnalytics->getParliamentaryPageData();

        $userTypeDetail = UserType::query()
            ->select('user_type.id', 'user_type.name', DB::raw('COUNT(users.id) as user_type__count'))
            ->leftJoin('users', 'users.user_type_id', '=', 'user_type.id')
            ->groupBy('user_type.id', 'user_type.name')
            ->orderBy('user_type.name')
            ->get();

        $activeStartups = ElectionStartupDetail::query()
            ->select('election_startup_detail.*', 'election_type.name as election_type_name')
            ->join('election_type', 'election_type.id', '=', 'election_startup_detail.election_type_id')
            ->orderByDesc('election_startup_detail.status')
            ->orderByDesc('election_startup_detail.id')
            ->take(6)
            ->get();

        return [
            'systemStats' => [
                'regions' => (int) Region::count(),
                'constituencies' => (int) Constituency::count(),
                'electoral_areas' => (int) ElectoralArea::count(),
                'polling_stations' => (int) PollingStation::count(),
                'registered_voters' => (int) PollingStation::sum('total_voters'),
                'field_users' => (int) User::count(),
                'administrators' => (int) SuperAdmin::count(),
            ],
            'userTypeDetail' => $userTypeDetail,
            'activeStartups' => $activeStartups,
            'presidentialElectionTypeId' => $this->nationalDashboard->getPresidentialElectionTypeId(),
            'presidential' => $presidential,
            'parliamentary' => $parliamentary,
        ];
    }
}
