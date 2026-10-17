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
        //
    })->create();
