<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireUserType
{
    public function handle(Request $request, Closure $next, ...$allowedTypes): Response
    {
        $user = $request->user();
        abort_unless($user && in_array((int) $user->user_type_id, array_map('intval', $allowedTypes), true), 403);

        return $next($request);
    }
}
