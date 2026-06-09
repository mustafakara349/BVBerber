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
            AuditLog::create([
                'user_id' => $request->user()?->id,
                'action' => strtolower($request->method()) . ':' . $request->path(),
                'model_type' => 'http_request',
                'model_id' => 0,
                'old_values' => null,
                'new_values' => json_encode($request->except(['password', 'password_confirmation', '_token'])),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
