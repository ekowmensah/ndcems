<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $guard = data_get($exception->guards(), 0);
        switch ($guard) {
            case 'superAdmin':
                $url =config('config.SuperAdminUrlPrefix').'/login';
                break;
            /* case 'agent':
                $url = '/agent/login';
                break; */
               /*  case 'web':
                $url = '/login';
                break; */
            default:
                $url = '/login';
                break;
        }

        return redirect()->guest($url);
        //return redirect()->guest(route('login'));
    }
}
