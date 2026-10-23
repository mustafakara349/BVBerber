<?php

use App\Enums\UserStatus;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
| Panel bildirimleri: her personelin yalnızca kendi kanalı.
| Yalnızca aktif ve personel rolündeki kullanıcılar abone olabilir.
*/
Broadcast::channel('admin.notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId
        && $user->status === UserStatus::Active
        && in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist', 'barber'], true);
});

/*
| Sistem İşlem & Hata Günlükleri Canlı Akışı:
| Yalnızca super_admin ve owner rolüne sahip aktif yöneticiler abone olabilir.
*/
Broadcast::channel('audit-logs', function ($user) {
    return $user->status === UserStatus::Active
        && in_array($user->role?->slug, ['super_admin', 'owner'], true);
});

