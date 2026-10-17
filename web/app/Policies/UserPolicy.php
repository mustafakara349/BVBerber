<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist']);
    }

    /**
     * Determine whether the user can view the customer details.
     */
    public function view(User $user, User $targetUser): Response
    {
        if (!in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist'])) {
            return Response::deny('Müşteri bilgilerini görüntüleme yetkiniz bulunmamaktadır.');
        }

        if ($targetUser->role?->slug !== 'customer') {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can create customers.
     */
    public function create(User $user): bool
    {
        return in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist']);
    }

    /**
     * Determine whether the user can update the customer.
     */
    public function update(User $user, User $targetUser): Response
    {
        if (!in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist'])) {
            return Response::deny('Müşteri güncelleme yetkiniz bulunmamaktadır.');
        }

        if ($targetUser->role?->slug !== 'customer') {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }

    /**
     * Determine whether the user can delete the customer.
     */
    public function delete(User $user, User $targetUser): Response
    {
        // Yalnızca üst yönetim silebilir (super_admin, owner, manager).
        // Resepsiyonist ve berber rolleri müşteriyi silemez.
        if (!in_array($user->role?->slug, ['super_admin', 'owner', 'manager'])) {
            return Response::deny('Müşteri kaydı silme yetkisi yalnızca yöneticilere aittir.');
        }

        if ($targetUser->role?->slug !== 'customer') {
            return Response::denyAsNotFound();
        }

        return Response::allow();
    }
}
