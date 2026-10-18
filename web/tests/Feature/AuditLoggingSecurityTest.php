<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuditLoggingSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private User $adminUser;
    private User $barberUser;
    private User $customerUser;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.database' => 'bvapp_db',
        ]);

        $ownerRole = Role::firstOrCreate(['slug' => 'owner'], ['name' => 'Owner']);
        $barberRole = Role::firstOrCreate(['slug' => 'barber'], ['name' => 'Barber']);
        $customerRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);

        $suffix = Str::lower(Str::random(6));

        $this->adminUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $ownerRole->id,
            'first_name' => 'AdminAudit',
            'last_name'  => 'Test',
            'email'      => "admin_audit_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('AdminPass123!'),
            'status'     => 'active',
        ]);

        $this->barberUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $barberRole->id,
            'first_name' => 'BarberAudit',
            'last_name'  => 'Test',
            'email'      => "barber_audit_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('BarberPass123!'),
            'status'     => 'active',
        ]);

        $this->customerUser = User::create([
            'uuid'       => (string) Str::uuid(),
            'role_id'    => $customerRole->id,
            'first_name' => 'CustomerAudit',
            'last_name'  => 'Test',
            'email'      => "cust_audit_{$suffix}@bvberber.com",
            'phone'      => '555' . rand(1000000, 9999999),
            'password'   => Hash::make('CustPass123!'),
            'status'     => 'active',
        ]);
    }

    /**
     * Test sensitive field recursive redaction in AuditLog
     */
    public function test_sensitive_data_is_recursively_redacted_in_audit_logs(): void
    {
        $rawPayload = [
            'password' => 'SuperSecret123!',
            'password_confirmation' => 'SuperSecret123!',
            'otp' => '123456',
            'token' => 'plain_api_token_here',
            'nested' => [
                'current_password' => 'OldSecret999!',
                'cvv' => '123',
                'safe_field' => 'visible_value',
            ],
        ];

        $log = AuditLog::record(
            action: 'test.redaction',
            modelType: 'security_test',
            modelId: 1,
            newValues: $rawPayload,
            userId: $this->adminUser->id,
            status: 'success'
        );

        $this->assertNotNull($log);
        $savedValues = json_decode($log->new_values, true);
        $data = $savedValues['data'];

        $this->assertEquals('[REDACTED]', $data['password']);
        $this->assertEquals('[REDACTED]', $data['password_confirmation']);
        $this->assertEquals('[REDACTED]', $data['otp']);
        $this->assertEquals('[REDACTED]', $data['token']);
        $this->assertEquals('[REDACTED]', $data['nested']['current_password']);
        $this->assertEquals('[REDACTED]', $data['nested']['cvv']);
        $this->assertEquals('visible_value', $data['nested']['safe_field']);
    }

    /**
     * Test Web authentication logging (Login success, failure, logout)
     */
    public function test_web_auth_events_are_recorded_in_audit_log(): void
    {
        // 1. Successful Web Login
        $response = $this->post('/login', [
            'email' => $this->adminUser->email,
            'password' => 'AdminPass123!',
        ]);
        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login.success',
            'user_id' => $this->adminUser->id,
        ]);

        // 2. Web Logout
        $logoutRes = $this->actingAs($this->adminUser, 'web')->post('/logout');
        $logoutRes->assertRedirect('/login');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.logout',
            'user_id' => $this->adminUser->id,
        ]);

        // 3. Failed Web Login
        $failRes = $this->post('/login', [
            'email' => $this->adminUser->email,
            'password' => 'WrongPassword123!',
        ]);
        $failRes->assertSessionHasErrors();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login.failed',
        ]);
    }

    /**
     * Test RoleMiddleware records authorization denials
     */
    public function test_role_middleware_logs_authorization_denials(): void
    {
        // Barber tries to access an Admin/Manager-only route (/employees)
        $response = $this->actingAs($this->barberUser, 'web')->get('/employees');
        $response->assertStatus(403);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'authorization.denied',
            'user_id' => $this->barberUser->id,
            'model_type' => 'route',
        ]);
    }

    /**
     * Test Auditable trait tracks model lifecycle changes
     */
    public function test_auditable_trait_records_model_changes(): void
    {
        $settingKey = 'audit_test_setting_' . Str::random(6);

        // Model creation
        $setting = Setting::create([
            'setting_key' => $settingKey,
            'setting_value' => 'initial_value',
            'description' => 'Test setting for audit log',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'setting.created',
            'model_type' => Setting::class,
            'model_id' => $setting->id,
        ]);

        // Model update
        $setting->update(['setting_value' => 'updated_value']);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'setting.updated',
            'model_type' => Setting::class,
            'model_id' => $setting->id,
        ]);

        // Model deletion
        $settingId = $setting->id;
        $setting->delete();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'setting.deleted',
            'model_type' => Setting::class,
            'model_id' => $settingId,
        ]);
    }
}
