<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\Staff;
use App\Models\User;

class AppointmentPolicy
{
    public function before(User $user, string $_ability): bool|null
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero', 'barbero']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['admin_sucursal', 'cajero'])) {
            return true;
        }
        if ($user->hasRole('barbero')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');
            return $appointment->staff_id === $staffId;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero', 'barbero']);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        if ($user->hasAnyRole(['admin_sucursal', 'cajero'])) {
            return true;
        }
        if ($user->hasRole('barbero')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');
            return $appointment->staff_id === $staffId;
        }
        return false;
    }

    public function delete(User $user, Appointment $_appointment): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function forceDelete(User $user, Appointment $_appointment): bool
    {
        return false;
    }

    public function forceDeleteAny(User $_user): bool
    {
        return false;
    }

    public function restore(User $user, Appointment $_appointment): bool
    {
        return false;
    }

    public function restoreAny(User $_user): bool
    {
        return false;
    }

    public function replicate(User $user, Appointment $_appointment): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero']);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }
}
