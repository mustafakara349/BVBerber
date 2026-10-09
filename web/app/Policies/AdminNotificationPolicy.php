<?php

namespace App\Policies;

use App\Models\AdminNotification;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Her personel yalnızca kendisine ait bildirimleri görebilir/değiştirebilir.
 * Yetkisiz erişimde 404 döndürülür; böylece başka kullanıcılara ait
 * bildirim ID'lerinin varlığı dışarıya sızdırılmaz (ID enumeration koruması).
 */
class AdminNotificationPolicy
{
    public function manage(User $user, AdminNotification $notification): Response
    {
        return (int) $notification->user_id === (int) $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
