<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role?->slug, $roles)) {
            \App\Models\AuditLog::record(
                action: 'authorization.denied',
                modelType: 'route',
                modelId: 0,
                oldValues: null,
                newValues: [
                    'required_roles' => $roles,
                    'current_role' => $user?->role?->slug,
                    'route' => $request->path(),
                    'method' => $request->method(),
                ],
                userId: $user?->id,
                status: 'denied',
                details: 'User attempted to access restricted route without required role.'
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Insufficient role.',
                ], 403);
            }

            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
