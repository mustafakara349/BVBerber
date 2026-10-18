<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

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

            \App\Models\AuditLog::record(
                action: 'auth.mobile_login.success',
                modelType: 'user',
                modelId: $result['user']->id,
                oldValues: null,
                newValues: ['email' => $result['user']->email],
                userId: $result['user']->id,
                status: 'success',
                details: 'Mobile user authenticated successfully'
            );

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
            \App\Models\AuditLog::record(
                action: 'auth.mobile_login.failed',
                modelType: 'user',
                modelId: 0,
                oldValues: null,
                newValues: ['email' => $request->input('email')],
                userId: null,
                status: 'failed',
                details: 'Mobile login failed: ' . $e->getMessage()
            );

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
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults()],
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
            'newPassword' => ['required', 'string', 'different:currentPassword', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        if (!Hash::check($request->currentPassword, $user->password)) {
            \App\Models\AuditLog::record(
                action: 'auth.password_update.failed',
                modelType: 'user',
                modelId: $user->id,
                oldValues: null,
                newValues: null,
                userId: $user->id,
                status: 'failed',
                details: 'Current password verification failed during password update'
            );

            return $this->error('Mevcut şifre hatalı.', 400);
        }

        DB::transaction(function () use ($user, $request) {
            $user->update([
                'password' => Hash::make($request->newPassword),
            ]);

            // Revoke all other active personal access tokens; keep only the current device token
            $currentToken = $user->currentAccessToken();
            if ($currentToken instanceof PersonalAccessToken && !empty($currentToken->id)) {
                $user->tokens()->where('id', '!=', $currentToken->id)->delete();
            } else {
                // If there is no specific personal access token, revoke all tokens to be safe
                $user->tokens()->delete();
            }
        });

        \App\Models\AuditLog::record(
            action: 'auth.password_update.success',
            modelType: 'user',
            modelId: $user->id,
            oldValues: null,
            newValues: null,
            userId: $user->id,
            status: 'success',
            details: 'User successfully updated account password and revoked other device tokens'
        );

        return $this->success(null, 'Password updated');
    }

    /**
     * Upload profile photo with strict content validation and safe storage.
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->error('Unauthenticated', 401);
        }

        if (!$request->hasFile('photo')) {
            return $this->error('Photo file required', 400);
        }

        // 1. Structural file and raster image validation (rejects SVG, HTML, scripts)
        $validator = Validator::make($request->all(), [
            'photo' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:2048', // 2MB max
            ],
        ], [
            'photo.required' => 'Fotoğraf dosyası zorunludur.',
            'photo.file' => 'Geçersiz dosya formatı.',
            'photo.image' => 'Yüklenen dosya geçerli bir raster görsel olmalıdır.',
            'photo.mimes' => 'Yalnızca JPEG, PNG veya WebP formatında görsel yüklenebilir.',
            'photo.mimetypes' => 'Yalnızca geçerli JPEG, PNG veya WebP görseli yüklenebilir.',
            'photo.max' => 'Fotoğraf boyutu en fazla 2MB olabilir.',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 422);
        }

        $file = $request->file('photo');
        $realPath = $file->getRealPath();

        // 2. Deep server-side content verification (getimagesize inspects actual image dimensions and headers)
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false || !in_array($imageInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return $this->error('Yüklenen dosya geçerli bir raster görsel (JPEG, PNG veya WebP) formatında olmalıdır.', 422);
        }

        // 3. Finfo magic bytes verification against mime spoofing
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($realPath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

        if (!in_array($detectedMime, $allowedMimes, true)) {
            return $this->error('Geçersiz görsel türü tespit edildi.', 422);
        }

        // 4. Safe extension derived strictly from verified image content (never trust client extension)
        $safeExtension = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            default => 'jpg',
        };

        // 5. Generate cryptographically safe random filename (prevents overwrite & traversal)
        $safeFilename = Str::uuid()->toString() . '.' . $safeExtension;

        $oldPath = $user->profile_photo;

        // 6. Store using safe filename in non-executable storage
        $storedPath = $file->storeAs('profile_photos', $safeFilename, 'public');

        // 7. Update authenticated user's record
        $user->update([
            'profile_photo' => $storedPath,
        ]);

        // 8. Delete previous profile photo if present on the public disk
        if ($oldPath && $oldPath !== $storedPath && str_starts_with($oldPath, 'profile_photos/') && !str_contains($oldPath, '..')) {
            Storage::disk('public')->delete($oldPath);
        }

        return $this->success([
            'profileImageUrl' => $request->schemeAndHttpHost() . '/storage/' . $storedPath
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
            $deviceTypeInput = strtolower((string)$request->input('device_type', 'android'));
            $deviceType = match($deviceTypeInput) {
                'ios' => \App\Enums\DeviceType::Ios,
                'web' => \App\Enums\DeviceType::Web,
                default => \App\Enums\DeviceType::Android,
            };

            $user->devices()->updateOrCreate(
                ['push_token' => $token],
                [
                    'device_type' => $deviceType,
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
        $user = $request->user();
        if ($user) {
            \App\Models\AuditLog::record(
                action: 'auth.mobile_logout',
                modelType: 'user',
                modelId: $user->id,
                oldValues: null,
                newValues: null,
                userId: $user->id,
                status: 'success',
                details: 'Mobile user token revoked'
            );
        }

        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }
}
