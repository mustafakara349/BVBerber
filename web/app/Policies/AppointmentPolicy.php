<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any appointments.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist', 'barber']);
    }

    /**
     * Determine whether the user can view the appointment details.
     */
    public function view(User $user, Appointment $appointment): Response
    {
        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        // Müşteri: Yalnızca kendi randevusunu görebilir
        if ($user->isCustomer() || $user->role?->slug === 'customer') {
            return ((int) $appointment->customer_id === (int) $user->id)
                ? Response::allow()
                : Response::deny('Bu randevuyu görüntüleme yetkiniz bulunmamaktadır.');
        }

        // Berber: Yalnızca kendi personeline atanmış randevuyu görebilir
        if ($user->role?->slug === 'barber') {
            $employeeId = $user->employee?->id;
            return ($employeeId && (int) $appointment->employee_id === (int) $employeeId)
                ? Response::allow()
                : Response::deny('Bu randevuyu görüntüleme yetkiniz bulunmamaktadır.');
        }

        // Yönetici, Sahip, Resepsiyonist: Kendi şubelerindeki randevuları görebilir
        $activeBranchId = (int) (session('active_branch_id') ?? $user->employee?->branch_id ?? 1);
        if ($appointment->branch_id === $activeBranchId || in_array($user->role?->slug, ['owner', 'super_admin'])) {
            return Response::allow();
        }

        return Response::deny('Bu randevu farklı bir şubeye aittir.');
    }

    /**
     * Determine whether the user can create appointments.
     */
    public function create(User $user): bool
    {
        return in_array($user->role?->slug, ['super_admin', 'owner', 'manager', 'receptionist', 'barber']);
    }

    /**
     * Determine whether the user can update the appointment details.
     */
    public function update(User $user, Appointment $appointment): Response
    {
        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        // Berber: Sadece kendi randevusunu güncelleyebilir
        if ($user->role?->slug === 'barber') {
            $employeeId = $user->employee?->id;
            return ($employeeId && (int) $appointment->employee_id === (int) $employeeId)
                ? Response::allow()
                : Response::deny('Yalnızca size atanmış randevuları düzenleyebilirsiniz.');
        }

        // Yönetici ve resepsiyonist kendi şubesindeki randevuları düzenleyebilir
        $activeBranchId = (int) (session('active_branch_id') ?? $user->employee?->branch_id ?? 1);
        if ($appointment->branch_id === $activeBranchId || in_array($user->role?->slug, ['owner', 'super_admin'])) {
            return Response::allow();
        }

        return Response::deny('Bu randevuyu düzenleme yetkiniz bulunmamaktadır.');
    }

    /**
     * Determine whether the user can update appointment status (confirm, cancel, complete, etc.).
     */
    public function updateStatus(User $user, Appointment $appointment): Response
    {
        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        // Müşteri: Sadece kendi randevusunu iptal edebilir
        if ($user->isCustomer() || $user->role?->slug === 'customer') {
            return ((int) $appointment->customer_id === (int) $user->id)
                ? Response::allow()
                : Response::deny('Bu randevunun durumunu değiştirme yetkiniz bulunmamaktadır.');
        }

        // Berber: Sadece kendi randevusunun durumunu değiştirebilir veya iptal edebilir
        if ($user->role?->slug === 'barber') {
            $employeeId = $user->employee?->id;
            return ($employeeId && (int) $appointment->employee_id === (int) $employeeId)
                ? Response::allow()
                : Response::deny('Başka bir berberin randevu durumunu değiştiremezsiniz.');
        }

        $activeBranchId = (int) (session('active_branch_id') ?? $user->employee?->branch_id ?? 1);
        if ($appointment->branch_id === $activeBranchId || in_array($user->role?->slug, ['owner', 'super_admin'])) {
            return Response::allow();
        }

        return Response::deny('Bu randevunun durumunu değiştirme yetkiniz bulunmamaktadır.');
    }

    /**
     * Determine whether the user can record a payment for the appointment.
     */
    public function managePayment(User $user, Appointment $appointment): Response
    {
        if ($user->isSuperAdmin()) {
            return Response::allow();
        }

        // Berber: Sadece kendi randevusu için ödeme alabilir / tamamlayabilir
        if ($user->role?->slug === 'barber') {
            $employeeId = $user->employee?->id;
            return ($employeeId && (int) $appointment->employee_id === (int) $employeeId)
                ? Response::allow()
                : Response::deny('Yalnızca kendi randevunuz için ödeme işlemi yapabilirsiniz.');
        }

        $activeBranchId = (int) (session('active_branch_id') ?? $user->employee?->branch_id ?? 1);
        if ($appointment->branch_id === $activeBranchId || in_array($user->role?->slug, ['owner', 'super_admin'])) {
            return Response::allow();
        }

        return Response::deny('Bu randevu için ödeme yetkiniz bulunmamaktadır.');
    }

    /**
     * Determine whether the user can delete a payment record.
     */
    public function deletePayment(User $user, Appointment $appointment): Response
    {
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return Response::allow();
        }

        if ($user->role?->slug === 'receptionist') {
            $activeBranchId = (int) (session('active_branch_id') ?? $user->employee?->branch_id ?? 1);
            return $appointment->branch_id === $activeBranchId
                ? Response::allow()
                : Response::deny('Farklı şubenin ödeme kaydı silinemez.');
        }

        // Berberler ödeme kaydını kesinlikle silemez
        return Response::deny('Ödeme kaydını silme yetkiniz bulunmamaktadır.');
    }
}
