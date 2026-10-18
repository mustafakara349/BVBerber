<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        try {
            $result = $this->authService->apiLogin($request->only('email', 'password'));
            $request->clearRateLimiter();
        } catch (\Throwable $e) {
            $request->hitRateLimiter();
            throw $e;
        }

        \App\Models\AuditLog::record(
            action: 'auth.api_login.success',
            modelType: 'user',
            modelId: $result['user']->id,
            oldValues: null,
            newValues: ['email' => $result['user']->email],
            userId: $result['user']->id,
            status: 'success',
            details: 'API user authenticated successfully'
        );

        return $this->success([
            'user' => new UserResource($result['user']->load('role')),
            'token' => $result['token'],
        ], 'Login successful');
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $customerRole = \App\Models\Role::where('slug', 'customer')->first();
        if (!$customerRole) {
            return $this->error('Customer role not found', 500);
        }

        $user = \App\Models\User::create([
            'role_id' => $customerRole->id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'status' => \App\Enums\UserStatus::Active,
        ]);

        \App\Models\AuditLog::record(
            action: 'auth.api_register.success',
            modelType: 'user',
            modelId: $user->id,
            oldValues: null,
            newValues: ['email' => $user->email],
            userId: $user->id,
            status: 'success',
            details: 'API user registered successfully'
        );

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user->load('role')),
            'token' => $token,
        ], 'Registration successful', 201);
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new UserResource($request->user()->load('role')),
            'Profile fetched successfully'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->apiLogout($request->user());

        return $this->success(null, 'Logged out successfully');
    }
}
