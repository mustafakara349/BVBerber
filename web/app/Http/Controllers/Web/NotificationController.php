<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // Group by title, body, type, and sent_at
        $rawNotifications = Notification::with('user')->latest('id')->get();
        
        $notifications = $rawNotifications->groupBy(function ($notif) {
            return $notif->title . '|' . $notif->body . '|' . $notif->type . '|' . ($notif->sent_at ? $notif->sent_at->format('Y-m-d H:i:s') : '');
        })
        ->map(function ($group) {
            $first = $group->first();
            $first->recipients_count = $group->count();
            $first->read_count = $group->where('is_read', true)->count();
            
            // Determine group label
            $first->target_group = 'Bireysel';
            if ($group->count() > 1) {
                $totalUsers = User::count();
                $totalCustomers = User::customers()->count();
                $totalStaff = User::staff()->count();
                
                if ($group->count() == $totalUsers) {
                    $first->target_group = 'Tüm Sistem Üyeleri';
                } elseif ($group->count() == $totalCustomers) {
                    $first->target_group = 'Tüm Müşteriler';
                } elseif ($group->count() == $totalStaff) {
                    $first->target_group = 'Tüm Personeller';
                } else {
                    $first->target_group = 'Grup (' . $group->count() . ' Alıcı)';
                }
            }
            
            $first->group_ids = $group->pluck('id')->toArray();
            return $first;
        })
        ->values()
        ->take(100);

        $users = User::orderBy('first_name')->get();

        // Calculate stats based on grouped notifications (batches)
        $totalBatches = Notification::selectRaw('title, body, type, sent_at')
            ->groupBy('title', 'body', 'type', 'sent_at')
            ->get()
            ->count();
            
        $unreadBatches = Notification::where('is_read', false)
            ->selectRaw('title, body, type, sent_at')
            ->groupBy('title', 'body', 'type', 'sent_at')
            ->get()
            ->count();

        $stats = [
            'total' => $totalBatches,
            'unread' => $unreadBatches,
            'read' => max(0, $totalBatches - $unreadBatches),
        ];

        return view('notifications.index', compact('notifications', 'users', 'stats'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|string|in:system,appointment,campaign,general',
        ]);

        $recipientType = $validated['user_id'];
        $title = $validated['title'];
        $body = $validated['body'];
        $type = $validated['type'];

        $targetUsers = [];

        if ($recipientType === 'all') {
            $targetUsers = User::all();
        } elseif ($recipientType === 'customers') {
            $targetUsers = User::customers()->get();
        } elseif ($recipientType === 'employees') {
            $targetUsers = User::staff()->get();
        } else {
            $targetUsers = User::where('id', $recipientType)->get();
        }

        $sentAt = now();
        $userIds = [];
        foreach ($targetUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'is_read' => false,
                'sent_at' => $sentAt,
            ]);
            $userIds[] = $user->id;
        }

        // Push Notification gönderimi
        try {
            $pushService = new \App\Services\FirebasePushService();
            $pushService->sendToMultipleUsers($userIds, $title, $body, [
                'type' => $type,
            ]);
        } catch (\Exception $e) {
            \Log::error("[FCM] Panelden push gönderimi başarısız: " . $e->getMessage());
        }

        return redirect()->route('notifications.index')->with('success', 'Bildirim(ler) başarıyla gönderildi.');
    }

    public function markAllRead()
    {
        Notification::where('is_read', false)->update(['is_read' => true]);

        return redirect()->route('notifications.index')->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }

    public function toggleRead(Request $request, Notification $notification)
    {
        if ($request->has('group_ids')) {
            $ids = json_decode($request->get('group_ids'), true);
            if (is_array($ids)) {
                $hasUnread = Notification::whereIn('id', $ids)->where('is_read', false)->exists();
                Notification::whereIn('id', $ids)->update(['is_read' => $hasUnread]);
                return redirect()->route('notifications.index')->with('success', 'Bildirim grubu durumu güncellendi.');
            }
        }

        $notification->update(['is_read' => !$notification->is_read]);
        return redirect()->route('notifications.index')->with('success', 'Bildirim durumu güncellendi.');
    }

    public function destroy(Request $request, Notification $notification)
    {
        if ($request->has('group_ids')) {
            $ids = json_decode($request->get('group_ids'), true);
            if (is_array($ids)) {
                Notification::whereIn('id', $ids)->delete();
                return redirect()->route('notifications.index')->with('success', 'Bildirim grubu silindi.');
            }
        }

        $notification->delete();
        return redirect()->route('notifications.index')->with('success', 'Bildirim silindi.');
    }
}
