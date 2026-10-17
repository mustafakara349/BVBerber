<?php

namespace App\Http\Controllers\Web;

use App\Enums\DeviceType;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Device;
use App\Models\Notification;
use App\Models\User;
use App\Services\FirebasePushService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastNotificationController extends Controller
{
    /**
     * Toplu duyuru ve anlık bildirim gönderme ekranı
     */
    public function index(): View
    {
        $totalDevices = Device::whereNotNull('push_token')->count();
        $androidDevices = Device::whereNotNull('push_token')->where('device_type', DeviceType::Android)->count();
        $iosDevices = Device::whereNotNull('push_token')->where('device_type', DeviceType::Ios)->count();
        $totalUsers = User::count();

        $recentBroadcasts = Notification::where('type', 'broadcast')
            ->selectRaw('title, body, count(*) as user_count, max(sent_at) as sent_at')
            ->groupBy('title', 'body')
            ->orderByDesc('sent_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_devices'   => $totalDevices,
            'android_devices' => $androidDevices,
            'ios_devices'     => $iosDevices,
            'total_users'     => $totalUsers,
        ];

        return view('notifications.broadcast', compact('stats', 'recentBroadcasts'));
    }

    /**
     * Bildirimi işler, FCM ile cihazlara fırlatır ve DB'ye kaydeder
     */
    public function send(Request $request, FirebasePushService $pushService): RedirectResponse
    {
        $validated = $request->validate([
            'title'  => 'required|string|max:150',
            'body'   => 'required|string|max:500',
            'target' => 'required|in:all,customers,last_30_days',
            'action' => 'nullable|in:general,campaign,appointment',
        ]);

        $title = trim($validated['title']);
        $body = trim($validated['body']);
        $target = $validated['target'];
        $action = $validated['action'] ?? 'general';

        // 1. Hedef kitleyi belirle
        $query = User::query();

        if ($target === 'customers') {
            $query->whereHas('role', function ($r) {
                $r->where('name', 'customer');
            });
        } elseif ($target === 'last_30_days') {
            $thirtyDaysAgo = now()->subDays(30);
            $query->whereHas('appointments', function ($a) use ($thirtyDaysAgo) {
                $a->where('start_at', '>=', $thirtyDaysAgo);
            });
        }

        $userIds = $query->pluck('id')->toArray();

        if (empty($userIds)) {
            return redirect()->back()->with('error', 'Seçilen hedef kitleye uygun kullanıcı bulunamadı.');
        }

        // 2. FCM Push Gönderimi (Arka planda FCM v1 API)
        $deliveredPushCount = 0;
        try {
            $payloadData = [
                'action' => match ($action) {
                    'campaign'    => 'open_campaign',
                    'appointment' => 'open_appointment',
                    default       => 'open_notifications',
                },
            ];

            $deliveredPushCount = $pushService->sendToMultipleUsers(
                $userIds,
                $title,
                $body,
                $payloadData
            );
        } catch (\Throwable $e) {
            \Log::error('Broadcast FCM error: ' . $e->getMessage());
        }

        // 3. Uygulama içi Bildirim tablosuna (In-App Notifications) toplu yaz
        $now = now();
        $notificationRecords = [];
        foreach ($userIds as $userId) {
            $notificationRecords[] = [
                'user_id'    => $userId,
                'title'      => $title,
                'body'       => $body,
                'type'       => 'broadcast',
                'data'       => json_encode([
                    'icon'   => match ($action) {
                        'campaign' => 'campaign',
                        default    => 'bell',
                    },
                    'action' => $action,
                ]),
                'is_read'    => false,
                'sent_at'    => $now,
                'created_at' => $now,
            ];
        }

        // Chunked insert ile DB performansını koru
        foreach (array_chunk($notificationRecords, 200) as $chunk) {
            Notification::insert($chunk);
        }

        $msg = sprintf(
            'Duyuru başarıyla gönderildi! %d kullanıcıya tanımlandı ve kayıtlı %d cihaza anlık push iletildi.',
            count($userIds),
            $deliveredPushCount
        );

        return redirect()->route('broadcast.index')->with('success', $msg);
    }
}
