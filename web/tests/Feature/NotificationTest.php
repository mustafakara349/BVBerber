<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

/**
 * NotificationTest
 *
 * Sprint 1 backlog'unun test karşılığı.
 * markAllRead metodunun yalnızca mevcut kullanıcının
 * bildirimlerini işaretlediğini doğrular.
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
            'email'      => $slug . rand(1, 9999) . '@test.com',
            'password'   => bcrypt('password'),
            'status'     => 'active',
        ]);
    }

    private function createNotification(int $userId, bool $isRead = false): \App\Models\Notification
    {
        return \App\Models\Notification::create([
            'user_id'  => $userId,
            'title'    => 'Test Bildirimi',
            'body'     => 'Test içeriği',
            'is_read'  => $isRead,
        ]);
    }

    /** @test */
    public function mark_all_read_only_affects_current_user_notifications(): void
    {
        $userA = $this->createUserWithRole('manager');
        $userB = $this->createUserWithRole('barber');

        // Kullanıcı A için 3 okunmamış bildirim
        $notifA1 = $this->createNotification($userA->id, false);
        $notifA2 = $this->createNotification($userA->id, false);
        $notifA3 = $this->createNotification($userA->id, false);

        // Kullanıcı B için 2 okunmamış bildirim
        $notifB1 = $this->createNotification($userB->id, false);
        $notifB2 = $this->createNotification($userB->id, false);

        // Kullanıcı A olarak markAllRead çağır
        $this->actingAs($userA)->post('/notifications/mark-all-read');

        // Kullanıcı A'nın bildirimleri okunmuş olmalı
        $this->assertDatabaseHas('notifications', ['id' => $notifA1->id, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $notifA2->id, 'is_read' => true]);
        $this->assertDatabaseHas('notifications', ['id' => $notifA3->id, 'is_read' => true]);

        // Kullanıcı B'nin bildirimleri ETKİLENMEMELİ
        $this->assertDatabaseHas('notifications', ['id' => $notifB1->id, 'is_read' => false]);
        $this->assertDatabaseHas('notifications', ['id' => $notifB2->id, 'is_read' => false]);
    }

    /** @test */
    public function mark_all_read_returns_redirect_to_notifications_index(): void
    {
        $user = $this->createUserWithRole('manager');
        $this->createNotification($user->id, false);

        $response = $this->actingAs($user)->post('/notifications/mark-all-read');

        $response->assertRedirect(route('notifications.index'));
        $response->assertSessionHas('success');
    }
}
