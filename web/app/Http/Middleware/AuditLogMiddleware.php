<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Exclude auth credential submission routes (handled with dedicated auth audit events)
            if ($request->is('login') || $request->is('api/*/auth/login') || $request->is('api/*/mobile/login') || $request->is('password/*') || $request->is('api/*/mobile/*password*')) {
                return $response;
            }

            $sanitizedPayload = AuditLog::redactSensitiveData($request->all());

            AuditLog::record(
                action: strtolower($request->method()) . ':' . $request->path(),
                modelType: 'http_request',
                modelId: 0,
                oldValues: null,
                newValues: $sanitizedPayload,
                userId: $request->user()?->id,
                status: ($response->isSuccessful() || $response->isRedirection()) ? 'success' : 'failed',
                details: 'HTTP ' . $request->method() . ' ' . $request->path() . ' (Status: ' . $response->getStatusCode() . ')'
            );
        }

        return $response;
    }
}
