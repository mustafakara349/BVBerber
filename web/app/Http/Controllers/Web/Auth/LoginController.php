<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function __construct(private AuthService $authService) {}

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        try {
            $this->authService->login(
                $request->only('email', 'password'),
                $request->boolean('remember')
            );
        } catch (ValidationException $e) {
            $request->hitRateLimiter();
            \App\Models\AuditLog::record(
                action: 'auth.login.failed',
                modelType: 'user',
                modelId: 0,
                oldValues: null,
                newValues: ['email' => $request->input('email')],
                userId: null,
                status: 'failed',
                details: 'Web login failed: invalid credentials or rate limited'
            );
            throw $e;
        } catch (\Throwable $e) {
            $request->hitRateLimiter();
            \App\Models\AuditLog::record(
                action: 'auth.login.failed',
                modelType: 'user',
                modelId: 0,
                oldValues: null,
                newValues: ['email' => $request->input('email')],
                userId: null,
                status: 'failed',
                details: 'Web login error: ' . $e->getMessage()
            );
            throw $e;
        }

        $request->clearRateLimiter();

        $request->session()->regenerate();

        \App\Models\AuditLog::record(
            action: 'auth.login.success',
            modelType: 'user',
            modelId: auth()->id() ?? 0,
            oldValues: null,
            newValues: ['email' => auth()->user()?->email],
            userId: auth()->id(),
            status: 'success',
            details: 'Web user authenticated successfully'
        );

        return redirect()->intended(route('dashboard'));
    }

    public function logout(): RedirectResponse
    {
        $userId = auth()->id();
        \App\Models\AuditLog::record(
            action: 'auth.logout',
            modelType: 'user',
            modelId: $userId ?? 0,
            oldValues: null,
            newValues: null,
            userId: $userId,
            status: 'success',
            details: 'Web user logged out'
        );

        $this->authService->logout();

        return redirect()->route('login');
    }
}
