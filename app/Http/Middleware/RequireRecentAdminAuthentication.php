<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRecentAdminAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = (int) $request->session()->get('admin_password_confirmed_at', 0);
        if ($confirmedAt < now()->subMinutes(15)->getTimestamp()) {
            $request->session()->put('url.intended', $request->headers->get('referer', route('SuperAdmin.home')));

            return redirect()->route('SuperAdmin.security.confirm');
        }

        return $next($request);
    }
}
