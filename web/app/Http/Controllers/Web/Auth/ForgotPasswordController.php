<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetOtp;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
            'email.email' => 'Geçerli bir e-posta adresi giriniz.',
            'email.exists' => 'Sistemde kayıtlı böyle bir e-posta adresi bulunamadı.'
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        // Sadece admin yetkisi olanların (panele giriş yetkisi olanlar) şifre sıfırlamasına izin ver
        if (!$user || !$user->isAdmin()) {
            return back()->withErrors(['email' => 'Bu panele erişim yetkiniz bulunmamaktadır. Lütfen mobil uygulamayı kullanın.'])->withInput();
        }

        $otp = rand(100000, 999999);

        // Eski OTP kodlarını sil
        PasswordResetOtp::where('email', $email)->delete();

        // Yeni OTP kodunu oluştur ve kaydet
        PasswordResetOtp::create([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // E-posta gönderimi
        Mail::to($email)->send(new PasswordResetOtpMail($otp));

        // Session'a kaydet ve doğrulama ekranına yönlendir
        session(['reset_email' => $email]);

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
            'otp.numeric' => 'Doğrulama kodu sadece sayılardan oluşmalıdır.',
            'otp.digits' => 'Doğrulama kodu 6 haneli olmalıdır.',
        ]);

        $email = session('reset_email');
        $otp = $request->otp;

        $otpRecord = PasswordResetOtp::where('email', $email)
            ->where('otp', $otp)
            ->first();

        if (!$otpRecord) {
            return back()->withErrors(['otp' => 'Girdiğiniz kod hatalı.']);
        }

        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            $otpRecord->delete();
            return back()->withErrors(['otp' => 'Bu kodun süresi dolmuş. Lütfen yeni bir kod isteyin.']);
        }

        // OTP doğrulandı, session'a onayı ekle
        session(['reset_otp_verified' => true, 'reset_otp' => $otp]);

        return redirect()->route('password.reset')->with('success', 'Kod başarıyla doğrulandı.');
    }

    /**
     * Yeni şifre belirleme formunu gösterir.
     */
    public function showResetForm()
    {
        if (!session()->has('reset_email') || !session()->has('reset_otp_verified')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Lütfen önce şifre sıfırlama adımlarını tamamlayınız.']);
        }

        return view('auth.passwords.reset');
    }

    /**
     * Yeni şifreyi kaydeder.
     */
    public function resetPassword(Request $request)
    {
        if (!session()->has('reset_email') || !session()->has('reset_otp_verified')) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifre en az 6 karakter olmalıdır.',
            'password.confirmed' => 'Şifreler birbiriyle eşleşmiyor.',
        ]);

        $email = session('reset_email');
        $otp = session('reset_otp');

        $otpRecord = PasswordResetOtp::where('email', $email)
            ->where('otp', $otp)
            ->first();

        if (!$otpRecord || Carbon::now()->isAfter($otpRecord->expires_at)) {
            return redirect()->route('password.request')->withErrors(['email' => 'Güvenlik kodu zaman aşımına uğradı veya geçersiz. Baştan başlayın.']);
        }

        // Kullanıcının şifresini güncelle
        $user = User::where('email', $email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                return back()->withErrors(['password' => 'Yeni şifreniz son şifrenizden farklı olmalıdır.']);
            }
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Kullanılan OTP kodunu veritabanından sil ve session temizle
        $otpRecord->delete();
        session()->forget(['reset_email', 'reset_otp_verified', 'reset_otp']);

        return redirect()->route('login')->with('success', 'Şifreniz başarıyla güncellendi. Yeni şifrenizle giriş yapabilirsiniz.');
    }
}
