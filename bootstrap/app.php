<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*')) {
                $status = 500;
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                    $status = 422;
                } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    $status = 401;
                }

                $message = $e->getMessage();
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $message = 'السجل المطلوب غير موجود.';
                    $status = 404;
                }

                // Log the error for the dashboard
                try {
                    \App\Models\ApiErrorLog::create([
                        'user_id' => auth()->id(),
                        'method' => $request->method(),
                        'url' => $request->fullUrl(),
                        'payload' => $request->except(['password', 'password_confirmation']),
                        'status_code' => $status,
                        'message' => $e->getMessage(),
                        'exception_class' => get_class($e),
                        'stack_trace' => collect($e->getTrace())->take(10)->toArray(),
                        'ip_address' => $request->ip(),
                        'device_id' => $request->header('X-Device-ID') ?? $request->input('device_id'),
                    ]);
                } catch (\Throwable $logError) {
                    // Fail silently to not break the actual error response
                    \Illuminate\Support\Facades\Log::error('Failed to log API error: ' . $logError->getMessage());
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors'  => $e instanceof \Illuminate\Validation\ValidationException ? $e->errors() : null,
                ], $status);
            }
        });
    })->create();
