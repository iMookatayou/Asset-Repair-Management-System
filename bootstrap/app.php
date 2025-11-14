<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Support\Str;
use App\Providers\AuthServiceProvider;
use App\Providers\RouteServiceProvider;
use App\Providers\AppServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // API group middleware: stateful + correlation id header
        $middleware->api(
            prepend: [
                \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
                \App\Http\Middleware\CorrelationId::class,
            ],
        );

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle authorization failures for web requests with toast
        $exceptions->render(function ($e, Request $request) {
            // Authorization/Access Denied: show toast and redirect back (web only)
            if (!$request->is('api/*') &&
                ($e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                 $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException)) {

                $message = $e->getMessage() ?: 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้';

                $redirectTo = $request->headers->get('referer')
                    ? redirect()->back()
                    : redirect()->route('dashboard');

                return $redirectTo->with('toast', [
                    'type' => 'error',
                    'message' => $message,
                    'timeout' => 3500,
                    'position' => 'tc',
                    'size' => 'md',
                ]);
            }

            if (! $request->is('api/*')) {
                return null; // use default rendering for other non-api exceptions
            }

            $cid = $request->attributes->get('correlation_id') ?? Str::uuid()->toString();

            $status = 500;
            $code   = 'INTERNAL_ERROR';
            $message = 'Internal server error';
            $errors  = null;

            // Validation
            if ($e instanceof ValidationException) {
                $status  = 422;
                $code    = 'VALIDATION_ERROR';
                $message = 'Validation failed';
                $errors  = $e->errors();
            }
            // Unauthenticated
            elseif ($e instanceof AuthenticationException) {
                $status  = 401;
                $code    = 'UNAUTHENTICATED';
                $message = 'Unauthenticated';
            }
            // Not found model
            elseif ($e instanceof ModelNotFoundException) {
                $status  = 404;
                $code    = 'NOT_FOUND';
                $message = 'Resource not found';
            }
            // Rate limit
            elseif ($e instanceof ThrottleRequestsException) {
                $status  = 429;
                $code    = 'RATE_LIMITED';
                $message = 'Too many requests';
            }
            // HTTP exceptions (403, 404, etc.)
            elseif ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getMessage() ?: match ($status) {
                    403 => 'Forbidden',
                    404 => 'Not found',
                    405 => 'Method not allowed',
                    429 => 'Too many requests',
                    default => 'HTTP error',
                };
                $code = match ($status) {
                    403 => 'FORBIDDEN',
                    404 => 'NOT_FOUND',
                    405 => 'METHOD_NOT_ALLOWED',
                    429 => 'RATE_LIMITED',
                    default => 'HTTP_ERROR',
                };
            }

            $payload = [
                'message'        => $message,
                'code'           => $code,
                'correlation_id' => $cid,
            ];
            if ($errors) {
                $payload['errors'] = $errors;
            }

            return response()->json($payload, $status)->withHeaders([
                'X-Correlation-ID' => $cid,
            ]);
        });
    })

    ->withProviders([
        AppServiceProvider::class,
        AuthServiceProvider::class,
        RouteServiceProvider::class,
    ])
    ->create();
