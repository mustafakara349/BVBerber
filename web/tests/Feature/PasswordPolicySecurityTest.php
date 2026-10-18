<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordPolicySecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        $ownerRole = Role::firstOrCreate(['slug' => 'owner'], ['name' => 'Owner']);
        $customerRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $suffix = Str::lower(Str::random(6));

        $this->adminUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $ownerRole->id,
            'first_name' => 'AdminPass',
            'last_name'  => 'Test',
            'email'      => "admin_pass_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('AdminPass123!'),
            'status'     => 'active',
        ]);

        $this->customerUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $customerRole->id,
            'first_name' => 'CustPass',
            'last_name'  => 'Test',
            'email'      => "cust_pass_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('CustOldPass123!'),
            'status'     => 'active',
        ]);
    }

    /**
     * Test API/Mobile Registration rejects weak passwords (< 8 chars, no numbers, no letters)
     */
    public function test_api_registration_rejects_weak_passwords(): void
    {
        $weakPasswords = [
            'short1',     // < 8 chars
            'allletters', // >= 8 chars, but no numbers
            '12345678',   // >= 8 chars, but no letters
        ];

        foreach ($weakPasswords as $weakPass) {
            $suffix = Str::lower(Str::random(5));
            $response = $this->postJson('/api/v1/mobile/register', [
                'name' => 'Test',
                'surname' => 'User',
                'email' => "weak_{$suffix}@test.com",
                'phone' => '555' . rand(1000000, 9999999),
                'password' => $weakPass,
                'password_confirmation' => $weakPass,
            ]);

            $response->assertStatus(422)
                     ->assertJsonValidationErrors('password');
        }
    }

    /**
     * Test API/Mobile Registration accepts compliant strong password
     */
    public function test_api_registration_accepts_compliant_password(): void
    {
        $suffix = Str::lower(Str::random(5));
        $validPass = 'StrongPass2026!';

        $response = $this->postJson('/api/v1/mobile/register', [
            'name' => 'Test',
            'surname' => 'User',
            'email' => "strong_{$suffix}@test.com",
            'phone' => '555' . rand(1000000, 9999999),
            'password' => $validPass,
            'password_confirmation' => $validPass,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true]);

        $createdUser = User::where('email', "strong_{$suffix}@test.com")->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue(Hash::check($validPass, $createdUser->password));
    }

    /**
     * Test Web Employee update rejects weak password and accepts strong password
     */
    public function test_web_employee_password_policy_enforcement(): void
    {
        $barberRole = Role::firstOrCreate(['slug' => 'barber'], ['name' => 'Barber']);
        $suffix = Str::lower(Str::random(5));

        $empUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $barberRole->id,
            'first_name' => 'Emp',
            'last_name'  => 'Test',
            'email'      => "emp_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('InitialPass123!'),
            'status'     => 'active',
        ]);

        $employee = Employee::create([
            'user_id' => $empUser->id,
            'branch_id' => 1,
            'employee_code' => 'EMP-' . rand(1000, 9999),
            'salary_type' => 'fixed',
            'salary_amount' => 20000,
            'commission_rate' => 10,
            'hire_date' => now()->toDateString(),
            'is_active' => true,
        ]);

        // Attempt update with weak password (too short)
        $weakRes = $this->actingAs($this->adminUser, 'web')
                        ->withSession(['active_branch_id' => 1])
                        ->put("/employees/{$employee->employee_code}", [
                            'first_name' => 'Emp',
                            'last_name' => 'Test',
                            'email' => $empUser->email,
                            'phone' => $empUser->phone,
                            'role_id' => $barberRole->id,
                            'salary_type' => 'fixed',
                            'salary_amount' => 20000,
                            'commission_rate' => 10,
                            'hire_date' => now()->toDateString(),
                            'password' => 'short1',
                        ]);
        $weakRes->assertSessionHasErrors('password');

        // Attempt update with compliant strong password
        $strongPass = 'NewEmpSecurePass2026!';
        $strongRes = $this->actingAs($this->adminUser, 'web')
                          ->withSession(['active_branch_id' => 1])
                          ->put("/employees/{$employee->employee_code}", [
                              'first_name' => 'Emp',
                              'last_name' => 'Test',
                              'email' => $empUser->email,
                              'phone' => $empUser->phone,
                              'role_id' => $barberRole->id,
                              'salary_type' => 'fixed',
                              'salary_amount' => 20000,
                              'commission_rate' => 10,
                              'hire_date' => now()->toDateString(),
                              'password' => $strongPass,
                          ]);
        $strongRes->assertRedirect('/employees');
        $this->assertTrue(Hash::check($strongPass, $empUser->fresh()->password));
    }

    /**
     * Test Web Customer update rejects weak password and properly hashes new password
     */
    public function test_web_customer_password_policy_and_secure_hashing(): void
    {
        // Weak password (< 8 chars)
        $weakRes = $this->actingAs($this->adminUser, 'web')
                        ->put("/customers/{$this->customerUser->id}", [
                            'first_name' => $this->customerUser->first_name,
                            'last_name'  => $this->customerUser->last_name,
                            'email'      => $this->customerUser->email,
                            'status'     => 'active',
                            'password'   => 'abc1',
                        ]);
        $weakRes->assertSessionHasErrors('password');

        // Strong password
        $newPass = 'BrandNewCustPass99!';
        $strongRes = $this->actingAs($this->adminUser, 'web')
                          ->put("/customers/{$this->customerUser->id}", [
                              'first_name' => $this->customerUser->first_name,
                              'last_name'  => $this->customerUser->last_name,
                              'email'      => $this->customerUser->email,
                              'status'     => 'active',
                              'password'   => $newPass,
                          ]);
        $strongRes->assertRedirect('/customers');

        // Verify database holds bcrypt hash and matches
        $freshCustomer = $this->customerUser->fresh();
        $this->assertNotEquals($newPass, $freshCustomer->password);
        $this->assertTrue(Hash::check($newPass, $freshCustomer->password));
    }

    /**
     * Test Mobile updatePassword rejects identical old password and requires strong password
     */
    public function test_mobile_update_password_enforces_policy_and_difference(): void
    {
        $token = $this->customerUser->createToken('test_device')->plainTextToken;

        // 1. Rejects same password
        $sameRes = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/mobile/me/update-password', [
                            'currentPassword' => 'CustOldPass123!',
                            'newPassword'     => 'CustOldPass123!',
                        ]);
        $sameRes->assertStatus(422)
                ->assertJsonValidationErrors('newPassword');

        // 2. Rejects weak password
        $weakRes = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/mobile/me/update-password', [
                            'currentPassword' => 'CustOldPass123!',
                            'newPassword'     => 'weakpass',
                        ]);
        $weakRes->assertStatus(422)
                ->assertJsonValidationErrors('newPassword');

        // 3. Accepts strong different password
        $strongPass = 'SuperNewPass2026!';
        $successRes = $this->withHeader('Authorization', 'Bearer ' . $token)
                           ->postJson('/api/v1/mobile/me/update-password', [
                               'currentPassword' => 'CustOldPass123!',
                               'newPassword'     => $strongPass,
                           ]);
        $successRes->assertStatus(200)
                   ->assertJson(['success' => true]);

        $this->assertTrue(Hash::check($strongPass, $this->customerUser->fresh()->password));
    }
}
