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
        $middleware->authenticateSessions();

        // 1. Trusted Reverse Proxies: Only trust designated reverse proxies/load balancers (never '*' blindly)
        $trustedProxies = env('TRUSTED_PROXIES');
        $proxies = $trustedProxies
            ? array_map('trim', explode(',', $trustedProxies))
            : ['127.0.0.1', '::1'];

        $middleware->trustProxies(at: $proxies);

        // 2. HTTP Security Headers (X-Content-Type-Options, CSP, Clickjacking, Referrer-Policy, HSTS)
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'audit' => \App\Http\Middleware\AuditLogMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Force JSON responses for API routes and AJAX/JSON consumers
        $exceptions->shouldRenderJsonWhen(function (\Illuminate\Http\Request $request, \Throwable $e): bool {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson() || $request->ajax() || $request->wantsJson();
        });

        // 2. Render safe, standardized JSON for API and branded HTML error pages for Web requests
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            // Secure sanitized JSON responses for API endpoints and AJAX callers (prevents trace and path leakage)
            if ($request->is('api/*') || $request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return null; // Standard Laravel validation error formatting
                }
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
                }
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json(['success' => false, 'message' => 'İstenen kaynak veya kayıt bulunamadı.'], 404);
                }
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException || $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                    return response()->json(['success' => false, 'message' => $e->getMessage() ?: 'Bu işlem için yetkiniz bulunmamaktadır.'], 403);
                }
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
                }

                /** @var \App\Services\ErrorTrackingService $tracker */
                $tracker = app(\App\Services\ErrorTrackingService::class);
                $referenceId = $tracker->logException($e, $request);

                return response()->json([
                    'success' => false,
                    'message' => 'Sunucu hatası oluştu. Lütfen daha sonra tekrar deneyiniz.',
                    'reference_id' => $referenceId,
                ], 500);
            }

            // Never intercept validation exceptions (must redirect back with $errors)
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            // Never intercept authentication exceptions on web (must redirect to login)
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return null;
            }

            /** @var \App\Services\ErrorTrackingService $tracker */
            $tracker = app(\App\Services\ErrorTrackingService::class);
            $statusCode = $tracker->resolveStatusCode($e);

            // Log server-side errors (5xx) with a unique reference tracking ID
            $referenceId = null;
            if ($tracker->isServerError($e) || $statusCode >= 500) {
                $referenceId = $tracker->logException($e, $request);
            }

            // Sanitize message: never leak SQLSTATE or raw framework route messages to end users
            $customMessage = null;
            if ($statusCode < 500 && $statusCode !== 404) {
                $rawMsg = $e->getMessage();
                if ($rawMsg && !str_starts_with($rawMsg, 'The route ') && !str_contains($rawMsg, 'could not be found') && !str_contains($rawMsg, 'No query results') && !str_contains($rawMsg, 'SQLSTATE') && !str_contains($rawMsg, 'SELECT ') && !str_contains($rawMsg, 'INSERT ')) {
                    $customMessage = $rawMsg;
                }
            }

            // Resolve rich metadata for this HTTP status code
            $metadata = \App\Support\Exceptions\WebErrorMetadata::resolve(
                statusCode: $statusCode,
                customMessage: $customMessage,
                referenceId: $referenceId
            );

            // Determine view: specific error view or generic layout
            $viewName = view()->exists("errors.{$statusCode}")
                ? "errors.{$statusCode}"
                : (view()->exists('errors.layout') ? 'errors.layout' : null);

            if ($viewName === null) {
                return null; // Fallback to default if error views not yet initialized
            }

            return response()->view($viewName, [
                'metadata' => $metadata,
                'exception' => $e,
                'referenceId' => $referenceId,
            ], $statusCode);
        });
    })->create();
