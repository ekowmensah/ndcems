<?php

namespace App\Console\Commands;

use App\Services\Operations\OperationalNotificationService;
use App\Services\Operations\SituationReportService;
use App\User;
use Illuminate\Console\Command;

class RefreshElectionOperations extends Command
{
    protected $signature = 'operations:refresh';
    protected $description = 'Refresh election alerts and generate role-scoped situation reports';

    public function handle(OperationalNotificationService $notifications, SituationReportService $reports): int
    {
        User::whereIn('user_type_id', [User::TYPE_NATIONAL_DIRECTOR, User::TYPE_REGIONAL_DIRECTOR, User::TYPE_CONSTITUENCY_DIRECTOR])
            ->each(function (User $user) use ($notifications, $reports) {
                $notifications->syncFor($user);
                $reports->generateIfDue($user);
            });
        $this->info('Election operations refreshed.');
        return self::SUCCESS;
    }
}
