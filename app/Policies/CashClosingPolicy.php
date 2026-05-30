<?php

namespace App\Policies;

use App\Models\CashClosing;
use App\Models\User;

class CashClosingPolicy
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

    public function view(User $user, CashClosing $cashClosing): bool
    {
        if ($user->hasAnyRole(['admin_sucursal', 'cajero'])) {
            return true;
        }
        // Barbero: solo lectura de sus propios cierres
        return $user->hasRole('barbero') && $cashClosing->user_id === $user->id;
    }

    // Solo admin y cajero pueden realizar cierres de caja
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero']);
    }

    public function update(User $user, CashClosing $cashClosing): bool
    {
        if ($user->hasRole('admin_sucursal')) {
            return true;
        }
        // Cajero puede corregir un cierre mientras no esté cerrado
        return $user->hasRole('cajero') && ! $cashClosing->is_closed;
    }

    public function delete(User $user, CashClosing $_cashClosing): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function forceDelete(User $user, CashClosing $_cashClosing): bool
    {
        return false;
    }

    public function forceDeleteAny(User $_user): bool
    {
        return false;
    }

    public function restore(User $user, CashClosing $_cashClosing): bool
    {
        return false;
    }

    public function restoreAny(User $_user): bool
    {
        return false;
    }

    public function replicate(User $user, CashClosing $_cashClosing): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }
}
