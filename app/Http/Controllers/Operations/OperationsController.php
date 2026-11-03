<?php

namespace App\Http\Controllers\Operations;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;

abstract class OperationsController extends Controller
{
    protected function viewContext(): array
    {
        $user = Auth::user();
        return match ((int) $user->user_type_id) {
            User::TYPE_NATIONAL_DIRECTOR => ['layout' => 'layouts.app_national_director', 'scopeLabel' => 'National'],
            User::TYPE_REGIONAL_DIRECTOR => ['layout' => 'layouts.app_national', 'scopeLabel' => 'Regional'],
            default => ['layout' => 'layouts.app_director', 'scopeLabel' => 'Constituency'],
        };
    }
}
