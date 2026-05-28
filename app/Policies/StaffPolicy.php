<?php

namespace App\Policies;

use App\Models\Staff;
use App\Models\User;

class StaffPolicy
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
        // Cajero puede ver el listado de barberos (solo lectura)
        return $user->hasAnyRole(['admin_sucursal', 'cajero']);
    }

    public function view(User $user, Staff $_staff): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero']);
    }

    // Solo admins pueden crear, editar o eliminar staff
    public function create(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function update(User $user, Staff $_staff): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function delete(User $user, Staff $_staff): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function forceDelete(User $user, Staff $_staff): bool
    {
        return false;
    }

    public function forceDeleteAny(User $_user): bool
    {
        return false;
    }

    public function restore(User $user, Staff $_staff): bool
    {
        return false;
    }

    public function restoreAny(User $_user): bool
    {
        return false;
    }

    public function replicate(User $user, Staff $_staff): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }
}
