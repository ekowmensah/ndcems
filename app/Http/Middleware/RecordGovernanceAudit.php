<?php

namespace App\Http\Middleware;

use App\Model\GovernanceAudit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class RecordGovernanceAudit
{
    private const SENSITIVE_FIELDS = [
        '_token', 'password', 'password_confirmation', 'secret', 'token', 'remember_token',
    ];

    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        $response = $next($request);

        if ($this->shouldAudit($request, $response)) {
            $this->record($request, $response, $guard);
        }

        return $response;
    }

    private function shouldAudit(Request $request, Response $response): bool
    {
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) || $response->getStatusCode() >= 400) {
            return false;
        }

        if ($request->hasSession() && ($request->session()->has('errors') || $request->session()->has('error'))) {
            return false;
        }

        $route = strtolower((string) optional($request->route())->getName());
        foreach (['ajax', 'verify', 'getregion', 'getconstituency', 'getelectral', 'getpollingstation', 'getpoliticalparty'] as $readOnlyMarker) {
            if (str_contains($route, $readOnlyMarker)) {
                return false;
            }
        }

        return true;
    }

    private function record(Request $request, Response $response, string $guard): void
    {
        try {
            $actor = Auth::guard($guard)->user();
            $route = optional($request->route())->getName();
            $payload = $this->sanitizePayload($request->except(self::SENSITIVE_FIELDS));

            GovernanceAudit::create([
                'actor_guard' => $guard,
                'actor_id' => $actor?->getAuthIdentifier(),
                'actor_name' => $actor?->name ?? $actor?->email,
                'event' => Str::of((string) $route)->after('.')->snake()->toString() ?: 'administrative_change',
                'route' => $route,
                'method' => $request->method(),
                'path' => $request->path(),
                'response_status' => $response->getStatusCode(),
                'request_data' => $payload ?: null,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Unable to record governance audit.', ['message' => $exception->getMessage()]);
        }
    }

    private function sanitizePayload(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (Str::contains(strtolower((string) $key), ['password', 'secret', 'token'])) {
                $payload[$key] = '[REDACTED]';
            } elseif ($value instanceof UploadedFile) {
                $payload[$key] = [
                    'filename' => $value->getClientOriginalName(),
                    'size' => $value->getSize(),
                    'mime_type' => $value->getClientMimeType(),
                ];
            } elseif (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            } elseif (!is_scalar($value) && $value !== null) {
                $payload[$key] = get_debug_type($value);
            }
        }

        return $payload;
    }
}
