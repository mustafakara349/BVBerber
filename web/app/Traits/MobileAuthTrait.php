<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

trait MobileAuthTrait
{
    /**
     * Mobile login endpoint.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $authService = app(\App\Services\AuthService::class);
        try {
            $result = $authService->apiLogin($request->only('email', 'password'));

            return $this->success([
                'user' => [
                    'id' => (string)$result['user']->id,
                    'email' => $result['user']->email,
                    'isActive' => $result['user']->status->value === 'active',
                    'name' => $result['user']->first_name,
                    'surname' => $result['user']->last_name,
                    'phone' => $result['user']->phone ?? '',
                    'profileImageUrl' => $result['user']->profile_photo ? $request->schemeAndHttpHost() . '/storage/' . $result['user']->profile_photo : '',
                    'role' => 'customer',
                ],
                'token' => $result['token'],
            ], 'Login successful');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    /**
     * Mobile registration endpoint.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $customerRole = \App\Models\Role::where('slug', 'customer')->first();
        if (!$customerRole) {
            return $this->error('Customer role not found', 500);
        }

        $user = \App\Models\User::create([
            'role_id' => $customerRole->id,
            'first_name' => $request->name,
            'last_name' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => \App\Enums\UserStatus::Active,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return $this->success([
            'user' => [
                'id' => (string)$user->id,
                'email' => $user->email,
                'isActive' => true,
                'name' => $user->first_name,
                'surname' => $user->last_name,
                'phone' => $user->phone ?? '',
                'profileImageUrl' => '',
                'role' => 'customer',
            ],
            'token' => $token,
        ], 'Registration successful', 201);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string',
            'surname' => 'required|string',
            'phone' => 'required|string',
        ]);

        $user->update([
            'first_name' => $data['name'],
            'last_name' => $data['surname'],
            'phone' => $data['phone'],
        ]);

        return $this->success(null, 'Profile updated');
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ]);

        if (!Hash::check($request->currentPassword, $user->password)) {
            return $this->error('Mevcut şifre hatalı.', 400);
        }

        $user->update([
            'password' => Hash::make($request->newPassword)
        ]);

        return $this->success(null, 'Password updated');
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$request->hasFile('photo')) {
            return $this->error('Photo file required', 400);
        }

        $path = $request->file('photo')->store('profile_photos', 'public');
        
        $user->update([
            'profile_photo' => $path
        ]);

        return $this->success([
            'profileImageUrl' => $request->schemeAndHttpHost() . '/storage/' . $path
        ], 'Photo uploaded successfully');
    }

    /**
     * Save FCM registration token.
     */
    public function saveToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->input('fcmToken');

        if ($token) {
            $user->devices()->updateOrCreate(
                ['push_token' => $token],
                [
                    'device_type' => \App\Enums\DeviceType::Ios,
                    'last_active_at' => now(),
                ]
            );
            return $this->success(null, 'Token saved');
        }

        return $this->error('Token required', 400);
    }

    /**
     * Logout user from this device.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }
}
