<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PasswordTokenRevocationTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private string $originalPassword = 'OldPassword123!';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        $role = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);
        $suffix = Str::lower(Str::random(6));

        $this->user = User::create([
            'uuid' => (string) Str::uuid(),
            'role_id' => $role->id,
            'first_name' => 'Token',
            'last_name' => 'Tester',
            'email' => "tokentester_{$suffix}@test.com",
            'phone' => '055' . rand(1000000, 9999999),
            'password' => Hash::make($this->originalPassword),
            'status' => 'active',
        ]);
    }

    /**
     * 1. Successful password change revokes all other device tokens while preserving current token.
     */
    public function test_successful_password_change_revokes_other_tokens_and_preserves_current_token(): void
    {
        // Generate three tokens: device 1 (current), device 2, device 3
        $token1 = $this->user->createToken('device-1-phone');
        $token2 = $this->user->createToken('device-2-tablet');
        $token3 = $this->user->createToken('device-3-laptop');

        $this->assertEquals(3, $this->user->tokens()->count());

        // Authenticate using token 1
        $newPassword = 'NewSecretPass2026!';
        $response = $this->withHeader('Authorization', 'Bearer ' . $token1->plainTextToken)
            ->postJson('/api/v1/mobile/me/update-password', [
                'currentPassword' => $this->originalPassword,
                'newPassword' => $newPassword,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // User's password must be updated
        $this->assertTrue(Hash::check($newPassword, $this->user->fresh()->password));

        // Tokens: Only token 1 should remain; token 2 and 3 must be deleted
        $remainingTokens = $this->user->tokens()->get();
        $this->assertCount(1, $remainingTokens);
        $this->assertEquals($token1->accessToken->id, $remainingTokens->first()->id);

        // Verify device 1 can STILL access protected endpoints
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token1->plainTextToken)
            ->getJson('/api/v1/mobile/me');
        $meResponse->assertStatus(200);

        // Clear auth guard cache so Sanctum re-evaluates the Bearer token against the database
        app('auth')->forgetGuards();

        // Verify device 2 and device 3 CANNOT authenticate
        $dev2Response = $this->withHeader('Authorization', 'Bearer ' . $token2->plainTextToken)
            ->getJson('/api/v1/mobile/me');
        $dev2Response->assertStatus(401);

        app('auth')->forgetGuards();

        $dev3Response = $this->withHeader('Authorization', 'Bearer ' . $token3->plainTextToken)
            ->getJson('/api/v1/mobile/me');
        $dev3Response->assertStatus(401);

        // Verify subsequent login with new password works
        $loginResponse = $this->postJson('/api/v1/mobile/login', [
            'email' => $this->user->email,
            'password' => $newPassword,
        ]);
        $loginResponse->assertStatus(200);
        $loginResponse->assertJson(['success' => true]);
    }

    /**
     * 2. Incorrect current password does not change password and does not revoke any tokens.
     */
    public function test_incorrect_current_password_does_not_revoke_tokens(): void
    {
        $token1 = $this->user->createToken('device-1');
        $token2 = $this->user->createToken('device-2');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token1->plainTextToken)
            ->postJson('/api/v1/mobile/me/update-password', [
                'currentPassword' => 'WrongPassword123!',
                'newPassword' => 'SomeNewPassword123!',
            ]);

        $response->assertStatus(400);

        // Password remains original
        $this->assertTrue(Hash::check($this->originalPassword, $this->user->fresh()->password));

        // All tokens remain active
        $this->assertEquals(2, $this->user->tokens()->count());

        // Device 2 is still authenticated
        $dev2Response = $this->withHeader('Authorization', 'Bearer ' . $token2->plainTextToken)
            ->getJson('/api/v1/mobile/me');
        $dev2Response->assertStatus(200);
    }

    /**
     * 3. Request without a specific personal access token (e.g. transient / mock) safely revokes all tokens.
     */
    public function test_request_without_specific_token_revokes_all_tokens_safely(): void
    {
        $token1 = $this->user->createToken('device-1');
        $token2 = $this->user->createToken('device-2');

        $this->assertEquals(2, $this->user->tokens()->count());

        // Simulate actingAs with null currentAccessToken (e.g. transient session)
        $newPassword = 'AnotherNewPass2026!';
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/mobile/me/update-password', [
                'currentPassword' => $this->originalPassword,
                'newPassword' => $newPassword,
            ]);

        $response->assertStatus(200);

        // Both tokens must be safely revoked since there was no specific current token to preserve
        $this->assertEquals(0, $this->user->tokens()->count());
    }

    /**
     * 4. Password reset flow continues to revoke ALL tokens.
     */
    public function test_api_password_reset_revokes_all_tokens(): void
    {
        $token1 = $this->user->createToken('device-1');
        $token2 = $this->user->createToken('device-2');

        // Request OTP
        $this->postJson('/api/v1/mobile/forgot-password', ['email' => $this->user->email]);

        // Directly update OTP in DB to known value for deterministic testing
        $otpCode = '123456';
        \App\Models\PasswordResetOtp::where('email', $this->user->email)->update([
            'otp' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $resetResponse = $this->postJson('/api/v1/mobile/reset-password', [
            'email' => $this->user->email,
            'otp' => $otpCode,
            'password' => 'ResetPass2026!',
            'password_confirmation' => 'ResetPass2026!',
        ]);

        $resetResponse->assertStatus(200);

        // ALL tokens must be deleted
        $this->assertEquals(0, $this->user->tokens()->count());

        // Old tokens cannot authenticate
        $checkResponse = $this->withHeader('Authorization', 'Bearer ' . $token1->plainTextToken)
            ->getJson('/api/v1/mobile/me');
        $checkResponse->assertStatus(401);
    }
}
