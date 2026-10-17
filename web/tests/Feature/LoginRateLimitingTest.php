<?php

namespace Tests\Feature;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginRateLimitingTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private User $secondAdminUser;
    private User $customerUser;
    private string $password = 'SecurePassword123!';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        $ownerRole = Role::firstOrCreate(['slug' => 'owner'], ['name' => 'Owner']);
        $customerRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $suffix = Str::lower(Str::random(6));

        $this->adminUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $ownerRole->id,
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => "admin_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make($this->password),
            'status' => 'active',
        ]);

        $this->secondAdminUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $ownerRole->id,
            'first_name' => 'SecondAdmin',
            'last_name' => 'User',
            'email' => "second_admin_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make($this->password),
            'status' => 'active',
        ]);

        $this->customerUser = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $customerRole->id,
            'first_name' => 'Customer',
            'last_name' => 'User',
            'email' => "customer_{$suffix}@bvberber.com",
            'phone' => '555' . rand(1000000, 9999999),
            'password' => Hash::make($this->password),
            'status' => 'active',
        ]);

        RateLimiter::clear(Str::transliterate(Str::lower($this->adminUser->email)) . '|127.0.0.1');
        RateLimiter::clear(Str::transliterate(Str::lower($this->secondAdminUser->email)) . '|127.0.0.1');
    }

    public function test_admin_can_login_with_valid_credentials_and_clears_throttle_key(): void
    {
        $response = $this->from(route('login'))->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => $this->password,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);

        // Throttle key should have 0 attempts
        $throttleKey = Str::transliterate(Str::lower($this->adminUser->email)) . '|127.0.0.1';
        $this->assertEquals(0, RateLimiter::attempts($throttleKey));
    }

    public function test_failed_login_returns_generic_error_without_revealing_account_existence(): void
    {
        // 1. Non-existent email
        $nonExistentResponse = $this->from(route('login'))->post(route('login'), [
            'email' => 'does_not_exist_xyz123@bvberber.com',
            'password' => 'WrongPassword123!',
        ]);

        $nonExistentResponse->assertRedirect(route('login'));
        $nonExistentResponse->assertSessionHasErrors(['email' => 'Geçersiz email veya şifre.']);

        // 2. Existing admin email with wrong password
        $existingAdminResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => 'WrongPassword123!',
        ]);

        $existingAdminResponse->assertRedirect(route('login'));
        $existingAdminResponse->assertSessionHasErrors(['email' => 'Geçersiz email veya şifre.']);

        // 3. Existing customer email with wrong password
        $customerResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $this->customerUser->email,
            'password' => 'WrongPassword123!',
        ]);

        $customerResponse->assertRedirect(route('login'));
        $customerResponse->assertSessionHasErrors(['email' => 'Geçersiz email veya şifre.']);

        // In all cases, the error message returned to client is identical: 'Geçersiz email veya şifre.'
    }

    public function test_web_login_is_throttled_after_five_consecutive_failed_attempts(): void
    {
        $throttleKey = Str::transliterate(Str::lower($this->adminUser->email)) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // Perform 5 failed attempts
        for ($i = 1; $i <= LoginRequest::MAX_ATTEMPTS; $i++) {
            $response = $this->from(route('login'))->post(route('login'), [
                'email' => $this->adminUser->email,
                'password' => 'WrongPassword!',
            ]);

            $response->assertRedirect(route('login'));
            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        $this->assertEquals(5, RateLimiter::attempts($throttleKey));

        // 6th attempt must be blocked by rate limiter
        $blockedResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => 'EvenIfPasswordWasRightNow!',
        ]);

        $blockedResponse->assertRedirect(route('login'));
        $blockedResponse->assertSessionHasErrors('email');

        $sessionErrors = session('errors')->get('email');
        $this->assertTrue(
            str_contains($sessionErrors[0], 'Too many login attempts') ||
            str_contains($sessionErrors[0], 'Çok fazla giriş denemesi')
        );
    }

    public function test_non_existent_account_is_also_throttled_identically_preventing_enumeration(): void
    {
        $ghostEmail = 'non_existent_' . Str::random(5) . '@bvberber.com';
        $throttleKey = Str::transliterate(Str::lower($ghostEmail)) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        for ($i = 1; $i <= LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->from(route('login'))->post(route('login'), [
                'email' => $ghostEmail,
                'password' => 'WrongPassword!',
            ]);
        }

        // 6th attempt on non-existent account must be locked out with identical error
        $blockedResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $ghostEmail,
            'password' => 'WrongPassword!',
        ]);

        $blockedResponse->assertRedirect(route('login'));
        $blockedResponse->assertSessionHasErrors('email');

        $sessionErrors = session('errors')->get('email');
        $this->assertTrue(
            str_contains($sessionErrors[0], 'Too many login attempts') ||
            str_contains($sessionErrors[0], 'Çok fazla giriş denemesi')
        );
    }

    public function test_throttling_cannot_be_bypassed_by_changing_casing_or_whitespace(): void
    {
        $baseEmail = $this->adminUser->email;
        $throttleKey = Str::transliterate(Str::lower($baseEmail)) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        $variations = [
            Str::upper($baseEmail),
            '  ' . $baseEmail . '  ',
            ucfirst($baseEmail),
            ' ' . Str::upper($baseEmail) . ' ',
            $baseEmail,
        ];

        foreach ($variations as $emailVariation) {
            $response = $this->from(route('login'))->post(route('login'), [
                'email' => $emailVariation,
                'password' => 'WrongPassword!',
            ]);

            $response->assertRedirect(route('login'));
        }

        // The single normalized throttleKey should now have 5 attempts
        $this->assertEquals(5, RateLimiter::attempts($throttleKey));

        // 6th attempt with lowercase must be throttled
        $blockedResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $baseEmail,
            'password' => $this->password,
        ]);

        $blockedResponse->assertRedirect(route('login'));
        $blockedResponse->assertSessionHasErrors('email');

        $sessionErrors = session('errors')->get('email');
        $this->assertTrue(
            str_contains($sessionErrors[0], 'Too many login attempts') ||
            str_contains($sessionErrors[0], 'Çok fazla giriş denemesi')
        );
        $this->assertGuest();
    }

    public function test_throttling_is_scoped_by_email_and_ip_so_different_user_on_shared_ip_is_not_locked_out(): void
    {
        $sharedIp = '192.168.10.50';

        $key1 = Str::transliterate(Str::lower($this->adminUser->email)) . '|' . $sharedIp;
        $key2 = Str::transliterate(Str::lower($this->secondAdminUser->email)) . '|' . $sharedIp;
        RateLimiter::clear($key1);
        RateLimiter::clear($key2);

        // Lock out adminUser from sharedIp
        for ($i = 1; $i <= LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $sharedIp])
                ->from(route('login'))
                ->post(route('login'), [
                    'email' => $this->adminUser->email,
                    'password' => 'WrongPassword!',
                ]);
        }

        $this->assertEquals(5, RateLimiter::attempts($key1));

        // Confirm adminUser is locked out
        $blockedResponse = $this->withServerVariables(['REMOTE_ADDR' => $sharedIp])
            ->from(route('login'))
            ->post(route('login'), [
                'email' => $this->adminUser->email,
                'password' => $this->password,
            ]);
        $blockedResponse->assertSessionHasErrors('email');

        // But secondAdminUser on the SAME shared IP must still be able to log in!
        $this->assertEquals(0, RateLimiter::attempts($key2));

        $secondUserResponse = $this->withServerVariables(['REMOTE_ADDR' => $sharedIp])
            ->from(route('login'))
            ->post(route('login'), [
                'email' => $this->secondAdminUser->email,
                'password' => $this->password,
            ]);

        $secondUserResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->secondAdminUser);
    }

    public function test_throttling_is_scoped_by_ip_so_attack_from_different_ip_does_not_lock_out_legitimate_ip(): void
    {
        $attackerIp = '198.51.100.99';
        $legitimateIp = '203.0.113.10';

        $attackerKey = Str::transliterate(Str::lower($this->adminUser->email)) . '|' . $attackerIp;
        $legitKey = Str::transliterate(Str::lower($this->adminUser->email)) . '|' . $legitimateIp;
        RateLimiter::clear($attackerKey);
        RateLimiter::clear($legitKey);

        // Attacker attempts 5 times from attackerIp
        for ($i = 1; $i <= LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => $attackerIp])
                ->from(route('login'))
                ->post(route('login'), [
                    'email' => $this->adminUser->email,
                    'password' => 'WrongPassword!',
                ]);
        }

        $this->assertEquals(5, RateLimiter::attempts($attackerKey));
        $this->assertEquals(0, RateLimiter::attempts($legitKey));

        // Legitimate user on legitimateIp can still log in successfully
        $legitResponse = $this->withServerVariables(['REMOTE_ADDR' => $legitimateIp])
            ->from(route('login'))
            ->post(route('login'), [
                'email' => $this->adminUser->email,
                'password' => $this->password,
            ]);

        $legitResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }

    public function test_successful_login_resets_failed_attempt_counter(): void
    {
        $throttleKey = Str::transliterate(Str::lower($this->adminUser->email)) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // Fail 3 times
        for ($i = 1; $i <= 3; $i++) {
            $this->from(route('login'))->post(route('login'), [
                'email' => $this->adminUser->email,
                'password' => 'WrongPassword!',
            ]);
        }

        $this->assertEquals(3, RateLimiter::attempts($throttleKey));

        // 4th attempt is successful
        $successResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => $this->password,
        ]);

        $successResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);

        // Rate limiter key must be cleared
        $this->assertEquals(0, RateLimiter::attempts($throttleKey));
    }

    public function test_login_recovers_after_decay_window_expires(): void
    {
        $throttleKey = Str::transliterate(Str::lower($this->adminUser->email)) . '|127.0.0.1';
        RateLimiter::clear($throttleKey);

        // Cause lockout
        for ($i = 1; $i <= LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->from(route('login'))->post(route('login'), [
                'email' => $this->adminUser->email,
                'password' => 'WrongPassword!',
            ]);
        }

        // Verify locked out
        $blockedResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => $this->password,
        ]);
        $blockedResponse->assertSessionHasErrors('email');
        $this->assertGuest();

        // Fast forward past the 60-second decay window
        $this->travel(LoginRequest::DECAY_SECONDS + 5)->seconds();

        // User can now log in successfully
        $recoveredResponse = $this->from(route('login'))->post(route('login'), [
            'email' => $this->adminUser->email,
            'password' => $this->password,
        ]);

        $recoveredResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->adminUser);
    }
}
