<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Sentry\State\Scope;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

use function Sentry\configureScope;

final class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [

    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [

    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (config('app.sentry.enabled') && app()->bound('sentry')) {
                configureScope(function (Scope $scope): void {
                    if (!app()->runningInConsole()) {
                        $user = Auth::user();
                        $scope->setUser(['id' => $user->id ?? 'guest']);
                    }
                });

                app('sentry')->captureException($e);
            }
        });

        $this->renderable(function (Exception $exception) {
            if ($exception instanceof HttpException && $exception->getStatusCode() >= 500) {
                return $this->handle500Error($exception);
            }
        });
    }

    protected function handle500Error(Exception $exception): Response
    {
        return Inertia::render('Error', ['statusCode' => '500']);
    }
}
