<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials, bool $remember = false): array
    {
        $user = User::with('role')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Geçersiz email veya şifre.'],
            ]);
        }

        if (!$user->isAdmin()) {
            throw ValidationException::withMessages([
                'email' => ['Bu panele erişim yetkiniz bulunmamaktadır.'],
            ]);
        }

        if ($user->status->value !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Hesabınız aktif değil.'],
            ]);
        }

        Auth::login($user, $remember);

        $user->update(['last_login_at' => now()]);

        return ['user' => $user];
    }

    public function apiLogin(array $credentials): array
    {
        $user = User::with('role')->where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        if ($user->status->value !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Account is not active.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function apiLogout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
