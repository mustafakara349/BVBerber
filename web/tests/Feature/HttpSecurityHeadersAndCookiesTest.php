<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class HttpSecurityHeadersAndCookiesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);
    }

    public function test_responses_contain_required_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);

        // 1. X-Content-Type-Options
        $response->assertHeader('X-Content-Type-Options', 'nosniff');

        // 2. Clickjacking protection (X-Frame-Options & CSP frame-ancestors)
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        // 3. Referrer-Policy
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. X-XSS-Protection (modern practice: 0 when CSP is present)
        $response->assertHeader('X-XSS-Protection', '0');

        // 5. Permissions-Policy
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // 6. Content-Security-Policy
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src", $csp);
        $this->assertStringContainsString("style-src", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("base-uri 'self'", $csp);
    }

    public function test_api_responses_also_contain_baseline_security_headers(): void
    {
        $response = $this->getJson('/api/v1/services');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('X-XSS-Protection', '0');
    }

    public function test_security_headers_middleware_does_not_overwrite_existing_custom_headers(): void
    {
        // Simulate a response that already defines its own custom X-Frame-Options or CSP
        $customResponse = new Response('Custom Content', 200, [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff-custom',
        ]);

        $request = Request::create('/custom-test', 'GET');
        $middleware = new \App\Http\Middleware\SecurityHeadersMiddleware();

        $processedResponse = $middleware->handle($request, function () use ($customResponse) {
            return $customResponse;
        });

        $this->assertEquals('DENY', $processedResponse->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff-custom', $processedResponse->headers->get('X-Content-Type-Options'));
    }

    public function test_secure_cookies_are_enforced_when_configured(): void
    {
        config([
            'session.secure' => true,
            'session.http_only' => true,
            'session.same_site' => 'lax',
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);

        // Find session cookie and XSRF-TOKEN cookie in Set-Cookie headers
        $cookies = $response->headers->getCookies();
        $this->assertNotEmpty($cookies);

        $sessionCookieName = config('session.cookie');
        $foundSessionCookie = false;
        $foundXsrfCookie = false;

        foreach ($cookies as $cookie) {
            if ($cookie->getName() === $sessionCookieName) {
                $foundSessionCookie = true;
                $this->assertTrue($cookie->isSecure(), 'Session cookie must be marked Secure');
                $this->assertTrue($cookie->isHttpOnly(), 'Session cookie must be marked HttpOnly');
                $this->assertEquals('lax', $cookie->getSameSite(), 'Session cookie SameSite must be lax');
            }

            if ($cookie->getName() === 'XSRF-TOKEN') {
                $foundXsrfCookie = true;
                $this->assertTrue($cookie->isSecure(), 'XSRF-TOKEN cookie must be marked Secure when session.secure is enabled');
                $this->assertEquals('lax', $cookie->getSameSite(), 'XSRF-TOKEN cookie SameSite must be lax');
            }
        }

        $this->assertTrue($foundSessionCookie, 'Session cookie was not found in response');
        $this->assertTrue($foundXsrfCookie, 'XSRF-TOKEN cookie was not found in response');
    }

    public function test_hsts_header_is_sent_when_request_is_secure(): void
    {
        // Request sent over HTTPS
        $response = $this->get('https://localhost/login');

        $response->assertStatus(200);
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_trusted_proxy_correctly_detects_https_from_designated_proxy(): void
    {
        // Request forwarded by trusted proxy (127.0.0.1) with X-Forwarded-Proto: https
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '127.0.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_untrusted_client_cannot_spoof_https_via_forwarded_headers(): void
    {
        // Arbitrary untrusted client IP attempting to spoof HTTPS forwarded headers
        $response = $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.195', // Untrusted external IP
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_PORT' => '443',
        ])->get('http://localhost/login');

        $response->assertStatus(200);

        // Since 203.0.113.195 is not in trusted proxies, the request is recognized as plain HTTP (no HSTS attached)
        $this->assertFalse(
            $response->headers->has('Strict-Transport-Security'),
            'Untrusted client spoofed HTTPS header must not be trusted'
        );
    }

    public function test_csrf_protection_and_login_flow_remain_fully_functional(): void
    {
        $adminRole = Role::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin']);
        $user = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $adminRole->id,
            'first_name' => 'Header',
            'last_name' => 'Tester',
            'email' => 'headertester_' . Str::random(6) . '@bvberber.com',
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make('Password123!'),
            'status' => 'active',
        ]);

        // Get login page to obtain valid session & CSRF token
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);

        // Submit login with valid credentials and CSRF
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        // Should successfully redirect to dashboard
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
