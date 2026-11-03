<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('capture-results', fn (User $user) => $user->canCaptureResults());
        Gate::define('approve-results', fn (User $user) => $user->canApproveResults());
        Gate::define('review-regional-results', fn (User $user) => $user->canReviewRegionalResults());
        Gate::define('review-national-results', fn (User $user) => $user->canReviewNationalResults());
    }
}
