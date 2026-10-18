<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and apply HTTP security headers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Generate a cryptographically secure 128-bit (16-byte) random nonce per request
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('cspNonce', $nonce);

        if (class_exists(\Illuminate\Support\Facades\View::class)) {
            \Illuminate\Support\Facades\View::share('cspNonce', $nonce);
        }

        if (class_exists(\Illuminate\Support\Facades\Vite::class)) {
            \Illuminate\Support\Facades\Vite::useCspNonce($nonce);
        }

        $response = $next($request);

        // 1. Prevent MIME confusion and MIME sniffing attacks
        if (!$response->headers->has('X-Content-Type-Options')) {
            $response->headers->set(
                'X-Content-Type-Options',
                config('security.headers.x_content_type_options', 'nosniff')
            );
        }

        // 2. Clickjacking protection: SAMEORIGIN allows same-origin embedding while preventing external framing
        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set(
                'X-Frame-Options',
                config('security.headers.x_frame_options', 'SAMEORIGIN')
            );
        }

        // 3. Referrer Policy: Send full URL on same-origin, only origin on cross-origin HTTPS, none on downgrade
        if (!$response->headers->has('Referrer-Policy')) {
            $response->headers->set(
                'Referrer-Policy',
                config('security.headers.referrer_policy', 'strict-origin-when-cross-origin')
            );
        }

        // 4. Disable obsolete and buggy legacy browser XSS filters in favor of modern CSP
        if (!$response->headers->has('X-XSS-Protection')) {
            $response->headers->set(
                'X-XSS-Protection',
                config('security.headers.x_xss_protection', '0')
            );
        }

        // 5. Restrict unused browser APIs
        if (!$response->headers->has('Permissions-Policy')) {
            $response->headers->set(
                'Permissions-Policy',
                config('security.headers.permissions_policy', 'camera=(), microphone=(), geolocation=(), payment=()')
            );
        }

        // 6. HTTP Strict Transport Security (HSTS)
        if (!$response->headers->has('Strict-Transport-Security')) {
            $hstsConfig = config('security.headers.hsts.enabled');
            $shouldSendHsts = ($hstsConfig === true) || ($request->isSecure() && $hstsConfig !== false);

            if ($shouldSendHsts) {
                $maxAge = config('security.headers.hsts.max_age', 31536000);
                $hstsValue = "max-age={$maxAge}";
                if (config('security.headers.hsts.include_subdomains', true)) {
                    $hstsValue .= '; includeSubDomains';
                }
                $response->headers->set('Strict-Transport-Security', $hstsValue);
            }
        }

        // 7. Content Security Policy (CSP)
        if (!$response->headers->has('Content-Security-Policy') && !$response->headers->has('Content-Security-Policy-Report-Only')) {
            $csp = $this->buildContentSecurityPolicy($nonce);
            if ($csp !== '') {
                $headerName = config('security.csp.report_only', false)
                    ? 'Content-Security-Policy-Report-Only'
                    : 'Content-Security-Policy';
                $response->headers->set($headerName, $csp);
            }
        }

        return $response;
    }

    /**
     * Build the Content Security Policy directive string.
     */
    protected function buildContentSecurityPolicy(?string $nonce = null): string
    {
        if (config('security.csp.enabled', true) === false) {
            return '';
        }

        $directives = config('security.csp.directives', []);
        if (empty($directives)) {
            return '';
        }

        // Add cryptographic per-request nonce to script-src if enabled
        if ($nonce && config('security.csp.nonce_enabled', true)) {
            if (isset($directives['script-src'])) {
                $directives['script-src'][] = "'nonce-{$nonce}'";
            }
        }

        // In local/testing environments, allow Vite development server and HMR WebSockets
        if (app()->environment('local', 'testing')) {
            $viteHosts = ['http://127.0.0.1:5173', 'http://localhost:5173'];
            $viteWs = ['ws://127.0.0.1:5173', 'ws://localhost:5173'];

            if (isset($directives['script-src'])) {
                $directives['script-src'] = array_values(array_unique(array_merge($directives['script-src'], $viteHosts)));
            }
            if (isset($directives['connect-src'])) {
                $directives['connect-src'] = array_values(array_unique(array_merge($directives['connect-src'], $viteHosts, $viteWs)));
            }
        }

        $policyParts = [];
        foreach ($directives as $directive => $sources) {
            if (is_array($sources)) {
                $sources = implode(' ', array_filter($sources));
            }
            if ($sources !== '') {
                $policyParts[] = "{$directive} {$sources}";
            }
        }

        return implode('; ', $policyParts);
    }
}
