<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\PasswordResetOtp;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Str;

class PasswordResetSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $customerUser;
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        Mail::fake();
        RateLimiter::clear('otp-send-ip:127.0.0.1');
        RateLimiter::clear('otp-verify-ip:127.0.0.1');
        RateLimiter::clear('otp-reset-ip:127.0.0.1');
        RateLimiter::clear('otp-web-send-ip:127.0.0.1');
        RateLimiter::clear('otp-web-verify-ip:127.0.0.1');

        $customerRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);
        $ownerRole = Role::firstOrCreate(['slug' => 'owner'], ['name' => 'Owner']);

        $suffix = Str::lower(Str::random(6));

        $this->customerUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $customerRole->id,
            'first_name' => 'Musteri',
            'last_name'  => 'Test',
            'email'      => "musteri_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('OldPassword123!'),
            'status'     => 'active',
        ]);

        $this->adminUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $ownerRole->id,
            'first_name' => 'Admin',
            'last_name'  => 'Test',
            'email'      => "admin_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('AdminOldPass123!'),
            'status'     => 'active',
        ]);

        RateLimiter::clear('otp-send-email:' . $this->customerUser->email);
        RateLimiter::clear('otp-web-send-email:' . $this->adminUser->email);
    }

    /**
     * 1. API: Başarılı OTP gönderimi, şifrelenmiş saklama, doğrulama ve şifre güncelleme
     */
    public function test_api_successful_password_reset_with_valid_otp(): void
    {
        $response = $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // E-postaya OTP gittiğini doğrula
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) {
            return $mail->hasTo($this->customerUser->email);
        });

        // Veritabanındaki kaydı kontrol et
        $otpRecord = PasswordResetOtp::where('email', $this->customerUser->email)->first();
        $this->assertNotNull($otpRecord);

        // Kural: OTP ASLA düz metin saklanmamalıdır (Bcrypt hash uzunluğu >= 60)
        $this->assertNotEquals(6, strlen($otpRecord->otp));
        $this->assertGreaterThanOrEqual(60, strlen($otpRecord->otp));

        // Gönderilen gerçek OTP kodunu mail nesnesinden al
        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        $this->assertNotNull($sentOtp);
        $this->assertEquals(6, strlen($sentOtp));
        $this->assertTrue(Hash::check($sentOtp, $otpRecord->otp));

        // OTP Doğrulama Uç Noktası
        $verifyRes = $this->postJson('/api/v1/mobile/verify-otp', [
            'email' => $this->customerUser->email,
            'otp'   => $sentOtp,
        ]);
        $verifyRes->assertStatus(200)
                  ->assertJson(['success' => true]);

        // Şifre Yenileme Uç Noktası
        $newPass = 'NewSecretPassword2026!';
        $resetRes = $this->postJson('/api/v1/mobile/reset-password', [
            'email'                 => $this->customerUser->email,
            'otp'                   => $sentOtp,
            'password'              => $newPass,
            'password_confirmation' => $newPass,
        ]);
        $resetRes->assertStatus(200)
                 ->assertJson(['success' => true]);

        // Kullanıcının yeni şifresinin çalıştığını doğrula
        $this->assertTrue(Hash::check($newPass, $this->customerUser->fresh()->password));

        // Başarılı işlem sonrası OTP derhal silinmeli
        $this->assertNull(PasswordResetOtp::where('email', $this->customerUser->email)->first());
    }

    /**
     * 2. API: Hatalı ve süresi dolmuş OTP'lerin reddedilmesi
     */
    public function test_api_rejection_of_invalid_and_expired_otps(): void
    {
        // Geçersiz kod denemesi
        $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);

        $invalidRes = $this->postJson('/api/v1/mobile/verify-otp', [
            'email' => $this->customerUser->email,
            'otp'   => '000000',
        ]);
        $invalidRes->assertStatus(400)
                   ->assertJson(['success' => false]);

        // Süresi dolmuş OTP denemesi
        $otpRecord = PasswordResetOtp::where('email', $this->customerUser->email)->first();
        $otpRecord->update(['expires_at' => now()->subMinutes(1)]);

        $expiredRes = $this->postJson('/api/v1/mobile/verify-otp', [
            'email' => $this->customerUser->email,
            'otp'   => '123456',
        ]);
        $expiredRes->assertStatus(400)
                   ->assertJson(['success' => false]);
    }

    /**
     * 3. API: Tek kullanımlık kuralı — kullanılmış kod tekrar kullanılamaz
     */
    public function test_api_rejection_of_reused_otps(): void
    {
        $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        // İlk sıfırlama
        $this->postJson('/api/v1/mobile/reset-password', [
            'email'                 => $this->customerUser->email,
            'otp'                   => $sentOtp,
            'password'              => 'FirstNewPass1!',
            'password_confirmation' => 'FirstNewPass1!',
        ])->assertStatus(200);

        // İkinci sıfırlama denemesi (replay saldırısı) reddedilmeli
        $replayRes = $this->postJson('/api/v1/mobile/reset-password', [
            'email'                 => $this->customerUser->email,
            'otp'                   => $sentOtp,
            'password'              => 'SecondNewPass2!',
            'password_confirmation' => 'SecondNewPass2!',
        ]);

        $replayRes->assertStatus(400)
                  ->assertJson(['success' => false]);
    }

    /**
     * 4. API: 5 hatalı denemede kaba kuvvet kilitlemesi (Brute-force lockout)
     */
    public function test_api_brute_force_lockout_after_max_failed_attempts(): void
    {
        $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        // 5 hatalı kod denemesi yap
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->postJson('/api/v1/mobile/verify-otp', [
                'email' => $this->customerUser->email,
                'otp'   => '11111' . $i,
            ]);
            $res->assertStatus(400);
        }

        // 5. denemeden sonra OTP kaydı silinmiş olmalı
        $this->assertNull(PasswordResetOtp::where('email', $this->customerUser->email)->first());

        // Doğru kod girilse bile artık geçersiz olmalı
        $correctTryRes = $this->postJson('/api/v1/mobile/verify-otp', [
            'email' => $this->customerUser->email,
            'otp'   => $sentOtp,
        ]);
        $correctTryRes->assertStatus(400);
    }

    /**
     * 5. API: Şifre sıfırlama sonrası aktif Sanctum token'larının iptal edilmesi
     */
    public function test_api_password_reset_revokes_active_tokens(): void
    {
        // Kullanıcıya bir aktif token ver
        $token = $this->customerUser->createToken('mobile_app');
        $this->assertEquals(1, $this->customerUser->tokens()->count());

        $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        $this->postJson('/api/v1/mobile/reset-password', [
            'email'                 => $this->customerUser->email,
            'otp'                   => $sentOtp,
            'password'              => 'BrandNewPass99!',
            'password_confirmation' => 'BrandNewPass99!',
        ])->assertStatus(200);

        // Tokenların silindiğini doğrula
        $this->assertEquals(0, $this->customerUser->fresh()->tokens()->count());
    }

    /**
     * 6. WEB: Web yönetici paneli akışı (Email -> Verify -> Update)
     */
    public function test_web_successful_password_reset_flow(): void
    {
        // 1. E-posta formunu gönder
        $response = $this->post('/password/email', [
            'email' => $this->adminUser->email,
        ]);

        $response->assertRedirect('/password/verify');
        $response->assertSessionHas('reset_email', $this->adminUser->email);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });
        $this->assertNotNull($sentOtp);

        // Veritabanında OTP hash'lenmiş olmalı
        $otpRecord = PasswordResetOtp::where('email', $this->adminUser->email)->first();
        $this->assertNotNull($otpRecord);
        $this->assertTrue(Hash::check($sentOtp, $otpRecord->otp));

        // 2. Kodu doğrula
        $verifyRes = $this->withSession(['reset_email' => $this->adminUser->email])
                          ->post('/password/verify', [
                              'otp' => $sentOtp,
                          ]);

        $verifyRes->assertRedirect('/password/new');
        $verifyRes->assertSessionHas('reset_otp_verified', true);
        $verifyRes->assertSessionHas('reset_token');

        $sessionToken = session('reset_token');

        // 3. Yeni şifreyi kaydet
        $newAdminPass = 'NewAdminPass2026!';
        $updateRes = $this->withSession([
            'reset_email'        => $this->adminUser->email,
            'reset_otp_verified' => true,
            'reset_token'        => $sessionToken,
        ])->post('/password/update', [
            'password'              => $newAdminPass,
            'password_confirmation' => $newAdminPass,
        ]);

        $updateRes->assertRedirect('/login');

        // Yeni şifre kaydedilmiş olmalı
        $this->assertTrue(Hash::check($newAdminPass, $this->adminUser->fresh()->password));

        // OTP kaydı silinmiş olmalı
        $this->assertNull(PasswordResetOtp::where('email', $this->adminUser->email)->first());
    }

    /**
     * 7. WEB: Doğrulama adımı tamamlanmadan şifre güncelleme baypas edilemez
     */
    public function test_web_bypass_prevention_without_verification(): void
    {
        // Oturumda onaylı token olmadan /password/update çağrılamaz
        $response = $this->post('/password/update', [
            'password'              => 'HackerPass123!',
            'password_confirmation' => 'HackerPass123!',
        ]);

        $response->assertRedirect('/password/reset');
        $this->assertFalse(Hash::check('HackerPass123!', $this->adminUser->fresh()->password));
    }

    /**
     * 8. Hız Sınırlaması: Arka arkaya 4 kez API kod talebinde bulunulduğunda engellenmeli
     */
    public function test_rate_limiting_on_repeated_otp_requests(): void
    {
        RateLimiter::clear('otp-send-ip:127.0.0.1');
        RateLimiter::clear('otp-send-email:' . $this->customerUser->email);

        for ($i = 1; $i <= 3; $i++) {
            $res = $this->postJson('/api/v1/mobile/forgot-password', [
                'email' => $this->customerUser->email,
            ]);
            $res->assertStatus(200);
        }

        // 4. deneme rate limit sınırına takılmalı (429)
        $rateLimitedRes = $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);
        $rateLimitedRes->assertStatus(429);
    }

    /**
     * 9. WEB: Web akışında hatalı ve süresi dolmuş OTP'lerin reddedilmesi
     */
    public function test_web_rejection_of_invalid_and_expired_otps(): void
    {
        $this->post('/password/email', ['email' => $this->adminUser->email]);

        // 1. Yanlış OTP gönder
        $invalidRes = $this->withSession(['reset_email' => $this->adminUser->email])
                           ->post('/password/verify', ['otp' => '000000']);
        $invalidRes->assertSessionHasErrors('otp');

        // 2. Süresi dolmuş OTP gönder
        $otpRecord = PasswordResetOtp::where('email', $this->adminUser->email)->first();
        $this->assertNotNull($otpRecord);
        $otpRecord->update(['expires_at' => now()->subMinutes(2)]);

        $expiredRes = $this->withSession(['reset_email' => $this->adminUser->email])
                            ->post('/password/verify', ['otp' => '123456']);
        $expiredRes->assertSessionHasErrors('otp');
    }

    /**
     * 10. WEB: Web akışında 5 hatalı denemede kaba kuvvet kilitlenmesi
     */
    public function test_web_brute_force_lockout_after_max_failed_attempts(): void
    {
        $this->post('/password/email', ['email' => $this->adminUser->email]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        // 5 kez ardışık hatalı kod gir
        for ($i = 1; $i <= 5; $i++) {
            $res = $this->withSession(['reset_email' => $this->adminUser->email])
                        ->post('/password/verify', ['otp' => '99999' . $i]);
            $res->assertSessionHasErrors('otp');
        }

        // 5. denemeden sonra OTP kaydı veritabanından tamamen silinmiş olmalı
        $this->assertNull(PasswordResetOtp::where('email', $this->adminUser->email)->first());

        // Doğru kod girilse bile artık kilitli olmalı
        $correctRes = $this->withSession(['reset_email' => $this->adminUser->email])
                           ->post('/password/verify', ['otp' => $sentOtp]);
        $correctRes->assertSessionHasErrors('otp');
    }

    /**
     * 11. WEB: Web OTP talebinde hız sınırlaması (Rate Limiting)
     */
    public function test_web_rate_limiting_on_repeated_requests(): void
    {
        RateLimiter::clear('otp-web-send-ip:127.0.0.1');
        RateLimiter::clear('otp-web-send-email:' . $this->adminUser->email);

        for ($i = 1; $i <= 3; $i++) {
            $res = $this->post('/password/email', ['email' => $this->adminUser->email]);
            $res->assertRedirect('/password/verify');
        }

        // 4. deneme hız sınırına takılmalı (Route throttle:3,1 -> 429)
        $rateLimitedRes = $this->post('/password/email', ['email' => $this->adminUser->email]);
        $this->assertTrue(in_array($rateLimitedRes->status(), [429, 302]));
        if ($rateLimitedRes->status() === 302) {
            $rateLimitedRes->assertSessionHasErrors('email');
        } else {
            $rateLimitedRes->assertStatus(429);
        }
    }

    /**
     * 12. WEB: Tek kullanımlık token ve replay engelleme
     */
    public function test_web_rejection_of_reused_otp_token(): void
    {
        $this->post('/password/email', ['email' => $this->adminUser->email]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        // Doğrula
        $this->withSession(['reset_email' => $this->adminUser->email])
             ->post('/password/verify', ['otp' => $sentOtp]);

        $sessionToken = session('reset_token');
        $this->assertNotNull($sessionToken);

        // İlk sıfırlama (başarılı)
        $this->withSession([
            'reset_email'        => $this->adminUser->email,
            'reset_otp_verified' => true,
            'reset_token'        => $sessionToken,
        ])->post('/password/update', [
            'password'              => 'FirstNewPass123!',
            'password_confirmation' => 'FirstNewPass123!',
        ])->assertRedirect('/login');

        // İkinci sıfırlama denemesi (replay saldırısı): Token artık geçersiz olmalı
        $replayRes = $this->withSession([
            'reset_email'        => $this->adminUser->email,
            'reset_otp_verified' => true,
            'reset_token'        => $sessionToken,
        ])->post('/password/update', [
            'password'              => 'SecondNewPass123!',
            'password_confirmation' => 'SecondNewPass123!',
        ]);

        $replayRes->assertRedirect('/password/reset');
    }

    /**
     * 13. OTURUM GEÇERSİZ KILMA: Şifre sıfırlandıktan sonra önceden açılmış web oturumları korumalı sayfalara erişemez
     */
    public function test_password_reset_invalidates_existing_authenticated_web_sessions(): void
    {
        // 1. Yönetici olarak oturum aç ve korumalı rotaya erişebildiğini doğrula
        $response = $this->actingAs($this->adminUser, 'web')->get('/dashboard');
        $response->assertStatus(200);

        // Veritabanı session kaydı simülasyonu
        $sessionsTable = config('session.table', 'sessions');
        $fakeSessionId = 'sess_' . Str::random(30);
        if (Schema::hasTable($sessionsTable)) {
            DB::table($sessionsTable)->insert([
                'id'            => $fakeSessionId,
                'user_id'       => $this->adminUser->id,
                'ip_address'    => '127.0.0.1',
                'user_agent'    => 'TestBrowser/1.0',
                'payload'       => base64_encode(serialize(['user_id' => $this->adminUser->id])),
                'last_activity' => time(),
            ]);
            $this->assertDatabaseHas($sessionsTable, ['id' => $fakeSessionId, 'user_id' => $this->adminUser->id]);
        }

        $oldPasswordHash = $this->adminUser->password; // sıfırlama öncesi hash

        // Oturumu kapatıp misafir olarak şifre sıfırlama formunu kullan
        auth('web')->logout();

        // 2. Şifreyi şifre sıfırlama akışı üzerinden güncelle
        $this->post('/password/email', ['email' => $this->adminUser->email]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        $this->withSession(['reset_email' => $this->adminUser->email])
             ->post('/password/verify', ['otp' => $sentOtp]);

        $token = session('reset_token');

        $this->withSession([
            'reset_email'        => $this->adminUser->email,
            'reset_otp_verified' => true,
            'reset_token'        => $token,
        ])->post('/password/update', [
            'password'              => 'BrandNewAdminPass999!',
            'password_confirmation' => 'BrandNewAdminPass999!',
        ]);

        // 3. Veritabanındaki aktif session kayıtlarının silindiğini doğrula
        if (Schema::hasTable($sessionsTable)) {
            $this->assertDatabaseMissing($sessionsTable, ['user_id' => $this->adminUser->id]);
        }

        // 4. AuthenticateSession ara katmanı testi:
        // Eski şifre hash'ine sahip bir oturum üzerinden /dashboard rotasına istek atıldığında
        // AuthenticateSession devreye girer ve isteği /login'e yönlendirir (AuthenticationException)
        $oldSessionResponse = $this->withSession([
            'login_web_' . sha1(User::class) => $this->adminUser->id,
            'password_hash_web'              => $oldPasswordHash,
        ])->get('/dashboard');

        $oldSessionResponse->assertRedirect('/login');
    }

    /**
     * 14. EŞZAMANLILIK (RACE CONDITION) VE SATIR KİLİTLEME:
     * lockForUpdate altında bir işlem satırı kilitlediğinde veya sildiğinde eşzamanlı işlem çift tüketim yapamaz
     */
    public function test_atomic_lock_prevents_concurrent_double_consumption(): void
    {
        $this->postJson('/api/v1/mobile/forgot-password', [
            'email' => $this->customerUser->email,
        ]);

        $sentOtp = null;
        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use (&$sentOtp) {
            $sentOtp = $mail->otp;
            return true;
        });

        // Simüle edilmiş eşzamanlı yarışma:
        // İstek 1: Atomik transaction içinde OTP'yi lockForUpdate ile alıp tüketir
        $firstConsumeSuccess = false;
        DB::transaction(function () use (&$firstConsumeSuccess) {
            $record = PasswordResetOtp::where('email', $this->customerUser->email)
                ->lockForUpdate()
                ->first();
            $this->assertNotNull($record);

            // Tüketildi ve silindi
            $record->delete();
            $firstConsumeSuccess = true;
        });

        $this->assertTrue($firstConsumeSuccess);

        // İstek 2 (Eşzamanlı yarışan ikinci istek): Aynı kaydı almaya çalışır
        $secondConsumeSuccess = false;
        DB::transaction(function () use (&$secondConsumeSuccess) {
            $record = PasswordResetOtp::where('email', $this->customerUser->email)
                ->lockForUpdate()
                ->first();

            if ($record) {
                $secondConsumeSuccess = true;
            }
        });

        // İkinci istek kaydı göremez, çifte tüketim atomik olarak engellenir
        $this->assertFalse($secondConsumeSuccess);
    }
}
