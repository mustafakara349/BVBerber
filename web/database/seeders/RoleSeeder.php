<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super_admin'],
            ['name' => 'Owner',       'slug' => 'owner'],
            ['name' => 'Manager',     'slug' => 'manager'],
            ['name' => 'Receptionist','slug' => 'receptionist'],
            ['name' => 'Barber',      'slug' => 'barber'],
            ['name' => 'Customer',    'slug' => 'customer'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
