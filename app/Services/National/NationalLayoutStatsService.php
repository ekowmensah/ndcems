<?php

namespace App\Services\National;

use App\Model\Constituency;
use App\Model\ElectoralArea;
use App\Model\PollingStation;

class NationalLayoutStatsService
{
    public function getStats(): array
    {
        try {
            return [
                'total_constituency' => (int) Constituency::count(),
                'total_voters' => (int) PollingStation::sum('total_voters'),
                'total_polling' => (int) PollingStation::count(),
                'total_electoral_area' => (int) ElectoralArea::count(),
            ];
        } catch (\Throwable $exception) {
            return [
                'total_constituency' => 0,
                'total_voters' => 0,
                'total_polling' => 0,
                'total_electoral_area' => 0,
            ];
        }
    }
}
