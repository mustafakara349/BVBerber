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
        $notifications = Notification::with('user')->latest('id')->take(100)->get();
        $users = User::orderBy('first_name')->get();

        $stats = [
            'total' => Notification::count(),
            'unread' => Notification::unread()->count(),
            'read' => Notification::where('is_read', true)->count(),
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

        foreach ($targetUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'body' => $body,
                'type' => $type,
                'is_read' => false,
                'sent_at' => now(),
            ]);
        }

        return redirect()->route('notifications.index')->with('success', 'Bildirim(ler) başarıyla gönderildi.');
    }

    public function markAllRead()
    {
        // Sadece oturum açan kullanıcının okunmamış bildirimlerini güncelle.
        // Önceki hata: tüm kullanıcıların bildirimleri işaretleniyordu.
        auth()->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return redirect()->route('notifications.index')->with('success', 'Tüm bildirimleriniz okundu olarak işaretlendi.');
    }

    public function toggleRead(Notification $notification)
    {
        $notification->update(['is_read' => !$notification->is_read]);
        return redirect()->route('notifications.index')->with('success', 'Bildirim durumu güncellendi.');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return redirect()->route('notifications.index')->with('success', 'Bildirim silindi.');
    }
}
