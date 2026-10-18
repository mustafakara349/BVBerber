<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetOtp;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\AuditLog;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Sistemde kayıtlı geçerli bir e-posta adresi girmelisiniz.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = Str::lower($request->email);
        $ipKey = 'otp-send-ip:' . $request->ip();
        $emailKey = 'otp-send-email:' . $email;

        // Rate limiting: 5 per minute per IP, 3 per 5 minutes per email
        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 3)) {
            $seconds = max(
                RateLimiter::availableIn($ipKey),
                RateLimiter::availableIn($emailKey)
            );
            return response()->json([
                'success' => false,
                'message' => "Çok fazla kod talebinde bulundunuz. Lütfen {$seconds} saniye sonra tekrar deneyin.",
            ], 429);
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($emailKey, 300);

        // Kriptografik olarak güvenli 6 haneli rastgele OTP üretimi
        $otp = (string) random_int(100000, 999999);

        // Bu e-posta adresine ait eski OTP kayıtlarını geçersiz kıl / temizle
        PasswordResetOtp::where('email', $email)->delete();

        // OTP'yi veritabanında ASLA düz metin saklama; hash'leyerek kaydet
        PasswordResetOtp::create([
            'email'      => $email,
            'otp'        => Hash::make($otp),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        // E-posta gönderimi (kullanıcıya ham OTP iletilir, loglanmaz)
        Mail::to($email)->send(new PasswordResetOtpMail($otp));

        AuditLog::record(
            action: 'auth.password_reset_otp.requested',
            details: ['email' => $email],
            outcome: 'success'
        );

        return response()->json([
            'success' => true,
            'message' => 'Şifre sıfırlama kodu e-posta adresinize gönderildi.',
        ]);
    }

    /**
     * Verify the OTP.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp'   => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Geçersiz veri.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = Str::lower($request->email);
        $verifyIpKey = 'otp-verify-ip:' . $request->ip();

        // Rate limiting: IP başına dakikada en fazla 10 doğrulama denemesi
        if (RateLimiter::tooManyAttempts($verifyIpKey, 10)) {
            $seconds = RateLimiter::availableIn($verifyIpKey);
            return response()->json([
                'success' => false,
                'message' => "Çok fazla doğrulama denemesi yaptınız. Lütfen {$seconds} saniye bekleyin.",
            ], 429);
        }
        RateLimiter::hit($verifyIpKey, 60);

        // Atomik kontrol ve satır kilitleme (Race-condition & brute-force bypass protection)
        $result = DB::transaction(function () use ($request, $email) {
            $otpRecord = PasswordResetOtp::where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$otpRecord) {
                return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Girdiğiniz kod hatalı.']];
            }

            // Süre aşımı kontrolü (10 dakika)
            if ($otpRecord->isExpired()) {
                $otpRecord->delete();
                return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Bu kodun süresi dolmuş. Lütfen yeni bir kod isteyin.']];
            }

            // Kaba kuvvet (brute force) koruması: En fazla 5 başarısız deneme
            if ($otpRecord->hasExceededAttempts(5)) {
                $otpRecord->delete();
                return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Çok fazla hatalı kod denemesi yapıldı. Güvenliğiniz için bu kod iptal edildi. Lütfen yeni bir kod talep edin.']];
            }

            // Hash kontrolü (güvenli hash doğrulama)
            if (!Hash::check((string) $request->otp, $otpRecord->otp)) {
                $otpRecord->increment('attempts');
                $remaining = 5 - $otpRecord->attempts;
                if ($remaining <= 0) {
                    $otpRecord->delete();
                    return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Çok fazla hatalı kod denemesi yapıldı. Bu kod iptal edildi. Lütfen yeni bir kod talep edin.']];
                }
                return ['status' => 400, 'payload' => ['success' => false, 'message' => "Girdiğiniz kod hatalı. Kalan deneme hakkı: {$remaining}"]];
            }

            return ['status' => 200, 'payload' => ['success' => true, 'message' => 'Kod doğrulandı.']];
        });

        if ($result['status'] === 200) {
            AuditLog::record(
                action: 'auth.password_reset_otp.verified',
                details: ['email' => $email],
                outcome: 'success'
            );
        } else {
            AuditLog::record(
                action: 'auth.password_reset_otp.failed',
                details: ['email' => $email, 'error' => $result['payload']['message'] ?? 'verification_failed'],
                outcome: 'failure'
            );
        }

        return response()->json($result['payload'], $result['status']);
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'otp'      => 'required|numeric|digits:6',
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lütfen geçerli şifre bilgileri girin.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = Str::lower($request->email);
        $resetIpKey = 'otp-reset-ip:' . $request->ip();

        if (RateLimiter::tooManyAttempts($resetIpKey, 10)) {
            $seconds = RateLimiter::availableIn($resetIpKey);
            return response()->json([
                'success' => false,
                'message' => "Çok fazla istek gönderildi. Lütfen {$seconds} saniye bekleyin.",
            ], 429);
        }
        RateLimiter::hit($resetIpKey, 60);

        // Atomik doğrulama ve tüketim (Race-condition & concurrent replay protection)
        $result = DB::transaction(function () use ($request, $email) {
            $otpRecord = PasswordResetOtp::where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$otpRecord || $otpRecord->isExpired()) {
                if ($otpRecord) {
                    $otpRecord->delete();
                }
                return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Geçersiz veya süresi dolmuş kod.']];
            }

            if ($otpRecord->hasExceededAttempts(5)) {
                $otpRecord->delete();
                return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Çok fazla hatalı deneme yapıldığı için bu kod iptal edildi. Lütfen baştan başlayın.']];
            }

            // Kod hash doğrulaması
            if (!Hash::check((string) $request->otp, $otpRecord->otp)) {
                $otpRecord->increment('attempts');
                if ($otpRecord->attempts >= 5) {
                    $otpRecord->delete();
                }
                return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Geçersiz veya süresi dolmuş kod.']];
            }

            // Kullanıcının şifresini güncelle
            $user = User::where('email', $email)->lockForUpdate()->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    return ['status' => 400, 'payload' => ['success' => false, 'message' => 'Yeni şifreniz son şifrenizden farklı olmalı.']];
                }
                $user->password = Hash::make($request->password);
                $user->save();

                // 1. Aktif tüm Sanctum token'larını sonlandır
                $user->tokens()->delete();

                // 2. Web oturumlarını sonlandır (database session store)
                $sessionsTable = config('session.table', 'sessions');
                if (Schema::hasTable($sessionsTable)) {
                    DB::table($sessionsTable)->where('user_id', $user->id)->delete();
                }
            }

            // Kullanılan OTP kodunu derhal sil (Tek kullanımlık kuralı)
            $otpRecord->delete();

            return ['status' => 200, 'payload' => ['success' => true, 'message' => 'Şifreniz başarıyla güncellendi.'], 'user_id' => $user?->id];
        });

        if ($result['status'] === 200) {
            AuditLog::record(
                action: 'auth.password_reset.success',
                modelType: User::class,
                modelId: $result['user_id'] ?? null,
                actorId: $result['user_id'] ?? null,
                details: ['email' => $email],
                outcome: 'success'
            );
        } else {
            AuditLog::record(
                action: 'auth.password_reset.failed',
                details: ['email' => $email, 'error' => $result['payload']['message'] ?? 'failed'],
                outcome: 'failure'
            );
        }

        return response()->json($result['payload'], $result['status']);
    }
}
