<?php

namespace App\Http\Controllers\Web;

use App\Enums\AdminNotificationCategory;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Sistem Bildirimleri (/notifications)
 *
 * Yalnızca oturum açmış personele ait iç sistem uyarılarını listeler ve yönetir.
 * Bildirim oluşturma/gönderme uç noktası bilinçli olarak YOKTUR; kayıtlar sadece
 * uygulama içi olaylardan (Observer'lar) üretilir.
 */
class NotificationController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['unread', 'read'])],
            'category' => ['nullable', Rule::enum(AdminNotificationCategory::class)],
        ]);

        $user = $request->user();

        $notifications = AdminNotification::query()
            ->ownedBy($user)
            ->when(($filters['status'] ?? null) === 'unread', fn ($q) => $q->unread())
            ->when(($filters['status'] ?? null) === 'read', fn ($q) => $q->read())
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->with('subject')
            ->latest('id')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $counts = AdminNotification::query()
            ->ownedBy($user)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN read_at IS NULL THEN 1 ELSE 0 END) as unread, MAX(id) as latest_id')
            ->first();

        $stats = [
            'total' => (int) $counts->total,
            'unread' => (int) $counts->unread,
            'read' => (int) $counts->total - (int) $counts->unread,
        ];

        return view('notifications.index', [
            'notifications' => $notifications,
            'stats' => $stats,
            'categories' => AdminNotificationCategory::cases(),
            'filters' => $filters,
            'latestId' => (int) $counts->latest_id,
        ]);
    }

    /** Topbar ve bildirim sayfasının canlı akışı (polling) için hafif JSON uç noktası. */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = AdminNotification::query()
            ->ownedBy($user)
            ->latest('id')
            ->limit(config('admin_notifications.feed_limit'))
            ->get();

        return response()->json([
            'unread_count' => AdminNotification::query()->ownedBy($user)->unread()->count(),
            'latest_id' => (int) ($items->first()?->id ?? 0),
            'items' => $items->map->toFeedArray()->values(),
        ]);
    }

    /** Bildirimi okundu yapar ve ilgili işlem ekranına güvenli şekilde yönlendirir. */
    public function open(AdminNotification $notification): RedirectResponse
    {
        Gate::authorize('manage', $notification);

        $notification->markAsRead();

        $target = $notification->safeActionUrl();

        return $target !== null
            ? redirect()->to($target)
            : redirect()->route('notifications.index');
    }

    public function toggleRead(AdminNotification $notification): RedirectResponse
    {
        Gate::authorize('manage', $notification);

        $notification->forceFill(['read_at' => $notification->isRead() ? null : now()])->save();

        return back()->with('success', 'Bildirim durumu güncellendi.');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        AdminNotification::query()
            ->ownedBy($request->user())
            ->unread()
            ->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }

    public function destroy(AdminNotification $notification): RedirectResponse
    {
        Gate::authorize('manage', $notification);

        $notification->delete();

        return back()->with('success', 'Bildirim silindi.');
    }

    public function destroyRead(Request $request): RedirectResponse
    {
        $deleted = AdminNotification::query()
            ->ownedBy($request->user())
            ->read()
            ->delete();

        return redirect()->route('notifications.index')->with('success', "{$deleted} okunmuş bildirim temizlendi.");
    }
}
