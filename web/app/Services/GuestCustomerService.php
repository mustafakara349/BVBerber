<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GuestCustomerService
{
    /**
     * Get an existing user by phone or create a new guest user.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @return User
     */
    public function findOrCreateGuestUser(string $firstName, string $lastName, string $phone): User
    {
        // Temizle
        $phone = preg_replace('/[^0-9]/', '', $phone);

        $user = User::where('phone', $phone)->first();

        if ($user) {
            return $user;
        }

        // Create shadow user
        $email = 'guest_' . $phone . '_' . Str::random(5) . '@bvbarber.com';

        // Get the Customer Role ID safely
        $customerRole = \App\Models\Role::where('slug', 'customer')->first();
        $roleId = $customerRole ? $customerRole->id : 6;

        return User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'email' => $email,
            'password' => Hash::make(Str::random(16)), // Güvenli rastgele şifre
            'is_guest' => true,
            'role_id' => $roleId, // Customer role
            'status' => \App\Enums\UserStatus::Active,
        ]);
    }
}
