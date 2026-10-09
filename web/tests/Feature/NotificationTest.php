<?php

namespace Tests\Feature;

use App\Enums\AdminNotificationCategory;
use App\Enums\AdminNotificationLevel;
use App\Models\AdminNotification;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Services\AdminNotifications\AdminAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * NotificationTest
 *
 * Sistem Bildirimleri (/notifications) sayfasının güvenlik ve davranış testleri:
 * - Her personel yalnızca kendi bildirimlerini görür/değiştirir.
 * - Bildirim gönderme (broadcast) uç noktası bulunmaz.
 * - Bildirimler servis aracılığıyla rol bazlı alıcılara üretilir.
 */
class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $slug): User
    {
        $role = Role::firstOrCreate(['slug' => $slug], ['name' => ucfirst($slug)]);

        return User::create([
            'uuid'       => (string) \Illuminate\Support\Str::uuid(),
            'role_id'    => $role->id,
            'first_name' => 'Test',
            'last_name'  => $slug,
            'email'      => $slug . rand(1, 99999) . '@test.com',
            'password'   => bcrypt('password'),
            'status'     => 'active',
        ]);
    }

    private function createNotification(int $userId, bool $isRead = false): AdminNotification
    {
        return AdminNotification::create([
            'user_id'  => $userId,
            'category' => AdminNotificationCategory::System,
            'event'    => 'system.test',
            'level'    => AdminNotificationLevel::Info,
            'title'    => 'Test Bildirimi',
            'body'     => 'Test içeriği',
            'read_at'  => $isRead ? now() : null,
        ]);
    }

    #[Test]
    public function mark_all_read_only_affects_current_user_notifications(): void
    {
        $userA = $this->createUserWithRole('manager');
        $userB = $this->createUserWithRole('barber');

        $notifA = $this->createNotification($userA->id);
        $notifB = $this->createNotification($userB->id);

        $this->actingAs($userA)
            ->post('/notifications/mark-all-read')
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success');

        $this->assertNotNull($notifA->fresh()->read_at);
        $this->assertNull($notifB->fresh()->read_at);
    }

    #[Test]
    public function user_cannot_toggle_or_delete_another_users_notification(): void
    {
        $owner = $this->createUserWithRole('manager');
        $intruder = $this->createUserWithRole('receptionist');
        $notification = $this->createNotification($owner->id);

        $this->actingAs($intruder)->patch(route('notifications.toggle-read', $notification))->assertNotFound();
        $this->actingAs($intruder)->delete(route('notifications.destroy', $notification))->assertNotFound();
        $this->actingAs($intruder)->get(route('notifications.open', $notification))->assertNotFound();

        $this->assertDatabaseHas('admin_notifications', ['id' => $notification->id, 'read_at' => null]);
    }

    #[Test]
    public function index_lists_only_own_notifications(): void
    {
        $userA = $this->createUserWithRole('manager');
        $userB = $this->createUserWithRole('manager');
        $this->createNotification($userA->id)->update(['title' => 'A kullanıcısının uyarısı']);
        $this->createNotification($userB->id)->update(['title' => 'B kullanıcısının uyarısı']);

        $this->actingAs($userA)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('A kullanıcısının uyarısı')
            ->assertDontSee('B kullanıcısının uyarısı');
    }

    #[Test]
    public function broadcast_endpoint_no_longer_exists(): void
    {
        $user = $this->createUserWithRole('super_admin');

        $this->actingAs($user)
            ->post('/notifications', ['user_id' => 'all', 'title' => 'x', 'body' => 'y', 'type' => 'general'])
            ->assertStatus(405);
    }

    #[Test]
    public function open_marks_as_read_and_rejects_external_redirects(): void
    {
        $user = $this->createUserWithRole('manager');
        $notification = $this->createNotification($user->id);
        $notification->update(['action_url' => '//evil.example.com']);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect(route('notifications.index'));

        $this->assertNotNull($notification->fresh()->read_at);
    }

    #[Test]
    public function service_delivers_alert_only_to_target_roles(): void
    {
        $manager = $this->createUserWithRole('manager');
        $barber = $this->createUserWithRole('barber');
        $customer = $this->createUserWithRole('customer');

        app(AdminNotificationService::class)->send(new AdminAlert(
            category: AdminNotificationCategory::Stock,
            event: 'stock.low',
            level: AdminNotificationLevel::Warning,
            title: 'Kritik Stok',
            body: 'Test',
            roles: AdminNotificationService::MANAGEMENT_ROLES,
        ));

        $this->assertDatabaseHas('admin_notifications', ['user_id' => $manager->id, 'event' => 'stock.low']);
        $this->assertDatabaseMissing('admin_notifications', ['user_id' => $barber->id]);
        $this->assertDatabaseMissing('admin_notifications', ['user_id' => $customer->id]);
    }
}
