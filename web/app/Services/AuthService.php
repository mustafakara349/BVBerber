<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials, bool $remember = false): array
    {
        $email = Str::lower(trim((string) ($credentials['email'] ?? '')));
        $password = (string) ($credentials['password'] ?? '');

        $user = User::with('role')->where('email', $email)->first();

        // Prevent timing discrepancy by always calculating a hash verification
        $passwordValid = $user ? Hash::check($password, $user->password) : false;
        if (!$user) {
            Hash::check($password, '$2y$12$WeQfJsfrjrwT6dov5rOWkewWzbW1vS7ZvbhX8HRhabj4WWcU56U4W');
        }

        if (!$user || !$passwordValid) {
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
        $email = Str::lower(trim((string) ($credentials['email'] ?? '')));
        $password = (string) ($credentials['password'] ?? '');

        $user = User::with('role')->where('email', $email)->first();

        // Prevent timing discrepancy by always calculating a hash verification
        $passwordValid = $user ? Hash::check($password, $user->password) : false;
        if (!$user) {
            Hash::check($password, '$2y$12$WeQfJsfrjrwT6dov5rOWkewWzbW1vS7ZvbhX8HRhabj4WWcU56U4W');
        }

        if (!$user || !$passwordValid) {
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
