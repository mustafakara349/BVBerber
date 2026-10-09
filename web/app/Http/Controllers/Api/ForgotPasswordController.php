<?php

namespace App\Http\Controllers\Api;

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
     * Send OTP to the user's email.
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Sistemde kayıtlı geçerli bir e-posta adresi girmelisiniz.', 'errors' => $validator->errors()], 422);
        }

        $email = $request->email;
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

        return response()->json(['success' => true, 'message' => 'Şifre sıfırlama kodu e-posta adresinize gönderildi.']);
    }

    /**
     * Verify the OTP.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veri.', 'errors' => $validator->errors()], 422);
        }

        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord) {
            return response()->json(['success' => false, 'message' => 'Girdiğiniz kod hatalı.'], 400);
        }

        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            $otpRecord->delete();
            return response()->json(['success' => false, 'message' => 'Bu kodun süresi dolmuş. Lütfen yeni bir kod isteyin.'], 400);
        }

        return response()->json(['success' => true, 'message' => 'Kod doğrulandı.']);
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|numeric|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Lütfen geçerli şifre bilgileri girin.', 'errors' => $validator->errors()], 422);
        }

        $otpRecord = PasswordResetOtp::where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord || Carbon::now()->isAfter($otpRecord->expires_at)) {
            return response()->json(['success' => false, 'message' => 'Geçersiz veya süresi dolmuş kod.'], 400);
        }

        // Kullanıcının şifresini güncelle
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Yeni şifreniz son şifrenizden farklı olmalı.'], 400);
            }
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Kullanılan OTP kodunu veritabanından sil
        $otpRecord->delete();

        return response()->json(['success' => true, 'message' => 'Şifreniz başarıyla güncellendi.']);
    }
}
