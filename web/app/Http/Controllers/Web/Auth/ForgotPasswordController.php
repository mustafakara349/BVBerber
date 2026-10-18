<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetOtp;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\AuditLog;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    /**
     * E-posta isteme formunu gösterir.
     */
    public function showEmailForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * E-posta adresine OTP gönderir.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'E-posta adresi zorunludur.',
            'email.email'    => 'Geçerli bir e-posta adresi giriniz.',
            'email.exists'   => 'Sistemde kayıtlı böyle bir e-posta adresi bulunamadı.',
        ]);

        $email = Str::lower($request->email);
        $ipKey = 'otp-web-send-ip:' . $request->ip();
        $emailKey = 'otp-web-send-email:' . $email;

        // Rate limiting: IP başına dakikada 5, e-posta başına 5 dakikada 3 istek
        if (RateLimiter::tooManyAttempts($ipKey, 5) || RateLimiter::tooManyAttempts($emailKey, 3)) {
            $seconds = max(
                RateLimiter::availableIn($ipKey),
                RateLimiter::availableIn($emailKey)
            );
            return back()->withErrors([
                'email' => "Çok fazla kod talebinde bulundunuz. Lütfen {$seconds} saniye sonra tekrar deneyin.",
            ])->withInput();
        }

        RateLimiter::hit($ipKey, 60);
        RateLimiter::hit($emailKey, 300);

        $user = User::where('email', $email)->first();

        // Sadece admin yetkisi olanların (panele giriş yetkisi olanlar) şifre sıfırlamasına izin ver
        if (!$user || !$user->isAdmin()) {
            AuditLog::record(
                action: 'auth.password_reset_otp.rejected',
                actorId: $user?->id,
                details: ['email' => $email, 'reason' => 'non_admin_panel_access'],
                outcome: 'failure'
            );
            return back()->withErrors(['email' => 'Bu panele erişim yetkiniz bulunmamaktadır. Lütfen mobil uygulamayı kullanın.'])->withInput();
        }

        // Kriptografik olarak güvenli 6 haneli rastgele OTP üretimi
        $otp = (string) random_int(100000, 999999);

        // Eski OTP kodlarını sil
        PasswordResetOtp::where('email', $email)->delete();

        // OTP'yi veritabanında ASLA düz metin saklama; hash'leyerek kaydet
        PasswordResetOtp::create([
            'email'      => $email,
            'otp'        => Hash::make($otp),
            'attempts'   => 0,
            'expires_at' => now()->addMinutes(10),
        ]);

        // E-posta gönderimi (ham OTP kullanıcıya iletilir)
        Mail::to($email)->send(new PasswordResetOtpMail($otp));

        AuditLog::record(
            action: 'auth.password_reset_otp.requested',
            modelType: User::class,
            modelId: $user->id,
            actorId: $user->id,
            details: ['email' => $email],
            outcome: 'success'
        );

        // Session'a kaydet ve önceki doğrulama durumlarını temizle
        session(['reset_email' => $email]);
        session()->forget(['reset_otp_verified', 'reset_token']);

        return redirect()->route('password.verify')->with('success', 'Şifre sıfırlama kodu e-posta adresinize gönderildi.');
    }

    /**
     * OTP doğrulama formunu gösterir.
     */
    public function showVerifyForm()
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Lütfen önce e-posta adresinizi giriniz.']);
        }

        return view('auth.passwords.verify');
    }

    /**
     * OTP'yi doğrular.
     */
    public function verifyOtp(Request $request)
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ], [
            'otp.required' => 'Doğrulama kodu alanı zorunludur.',
            'otp.numeric'  => 'Doğrulama kodu sadece sayılardan oluşmalıdır.',
            'otp.digits'   => 'Doğrulama kodu 6 haneli olmalıdır.',
        ]);

        $ipKey = 'otp-web-verify-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            $seconds = RateLimiter::availableIn($ipKey);
            return back()->withErrors(['otp' => "Çok fazla deneme yaptınız. Lütfen {$seconds} saniye bekleyin."]);
        }
        RateLimiter::hit($ipKey, 60);

        $email = Str::lower(session('reset_email'));

        // Atomik doğrulama ve satır kilitleme
        $result = DB::transaction(function () use ($request, $email) {
            $otpRecord = PasswordResetOtp::where('email', $email)
                ->lockForUpdate()
                ->first();

            if (!$otpRecord || $otpRecord->isExpired()) {
                if ($otpRecord) {
                    $otpRecord->delete();
                }
                return ['success' => false, 'error' => 'Bu kodun süresi dolmuş veya geçersiz. Lütfen yeni bir kod isteyin.'];
            }

            // Kaba kuvvet engelleme: 5 hatalı denemede OTP iptal edilir
            if ($otpRecord->hasExceededAttempts(5)) {
                $otpRecord->delete();
                return ['success' => false, 'error' => 'Çok fazla hatalı deneme yaptınız. Güvenliğiniz için bu kod iptal edildi. Lütfen yeni bir kod isteyin.'];
            }

            // Hash kontrolü
            if (!Hash::check((string) $request->otp, $otpRecord->otp)) {
                $otpRecord->increment('attempts');
                $remaining = 5 - $otpRecord->attempts;
                if ($remaining <= 0) {
                    $otpRecord->delete();
                    return ['success' => false, 'error' => 'Çok fazla hatalı deneme yaptınız. Bu kod iptal edildi. Lütfen yeni bir kod isteyin.'];
                }
                return ['success' => false, 'error' => "Girdiğiniz kod hatalı. Kalan deneme hakkı: {$remaining}"];
            }

            // Başarılı doğrulama: Tek kullanımlık güvenli token üret
            $resetToken = Str::random(64);
            $otpRecord->reset_token = hash('sha256', $resetToken);
            $otpRecord->save();

            return ['success' => true, 'reset_token' => $resetToken];
        });

        if (!$result['success']) {
            AuditLog::record(
                action: 'auth.password_reset_otp.failed',
                details: ['email' => $email, 'error' => $result['error']],
                outcome: 'failure'
            );
            return back()->withErrors(['otp' => $result['error']]);
        }

        AuditLog::record(
            action: 'auth.password_reset_otp.verified',
            details: ['email' => $email],
            outcome: 'success'
        );

        session([
            'reset_otp_verified' => true,
            'reset_token'        => $result['reset_token'],
        ]);

        return redirect()->route('password.reset')->with('success', 'Kod başarıyla doğrulandı.');
    }

    /**
     * Yeni şifre belirleme formunu gösterir.
     */
    public function showResetForm()
    {
        if (!session()->has('reset_email') || !session()->has('reset_otp_verified') || !session()->has('reset_token')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Lütfen önce şifre sıfırlama adımlarını tamamlayınız.']);
        }

        return view('auth.passwords.reset');
    }

    /**
     * Yeni şifreyi kaydeder.
     */
    public function resetPassword(Request $request)
    {
        if (!session()->has('reset_email') || !session()->has('reset_otp_verified') || !session()->has('reset_token')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'password.required'  => 'Şifre alanı zorunludur.',
            'password.confirmed' => 'Şifreler birbiriyle eşleşmiyor.',
        ]);

        $email = Str::lower(session('reset_email'));
        $token = session('reset_token');

        // Atomik kontrol ve şifre güncelleme (Race condition & concurrent consumption protection)
        $result = DB::transaction(function () use ($request, $email, $token) {
            $otpRecord = PasswordResetOtp::where('email', $email)
                ->lockForUpdate()
                ->first();

            // Tek kullanımlık token ve süre kontrolü
            if (!$otpRecord || $otpRecord->isExpired() || !hash_equals((string) $otpRecord->reset_token, hash('sha256', (string) $token))) {
                if ($otpRecord) {
                    $otpRecord->delete();
                }
                return ['success' => false, 'redirect' => 'request', 'error' => 'Güvenlik kodu zaman aşımına uğradı veya geçersiz. Baştan başlayın.'];
            }

            // Kullanıcının şifresini güncelle
            $user = User::where('email', $email)->lockForUpdate()->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    return ['success' => false, 'redirect' => 'back', 'error' => 'Yeni şifreniz son şifrenizden farklı olmalıdır.'];
                }
                $user->password = Hash::make($request->password);
                $user->save();

                // 1. Sanctum API token'larını sonlandır
                $user->tokens()->delete();

                // 2. Web oturum tablosundaki aktif oturumları temizle
                $sessionsTable = config('session.table', 'sessions');
                if (Schema::hasTable($sessionsTable)) {
                    DB::table($sessionsTable)->where('user_id', $user->id)->delete();
                }
            }

            // Kullanılan OTP kaydını derhal sil
            $otpRecord->delete();

            return ['success' => true, 'user_id' => $user?->id];
        });

        if (!$result['success']) {
            AuditLog::record(
                action: 'auth.password_reset.failed',
                details: ['email' => $email, 'error' => $result['error']],
                outcome: 'failure'
            );
            if (($result['redirect'] ?? '') === 'back') {
                return back()->withErrors(['password' => $result['error']]);
            }
            session()->forget(['reset_email', 'reset_otp_verified', 'reset_token']);
            return redirect()->route('password.request')->withErrors(['email' => $result['error']]);
        }

        AuditLog::record(
            action: 'auth.password_reset.success',
            modelType: User::class,
            modelId: $result['user_id'] ?? null,
            actorId: $result['user_id'] ?? null,
            details: ['email' => $email],
            outcome: 'success'
        );

        // Geçerli oturumu temizle ve session ID'sini yenile
        session()->forget(['reset_email', 'reset_otp_verified', 'reset_token']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
    }
}
