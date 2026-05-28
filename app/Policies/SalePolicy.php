<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\Staff;
use App\Models\User;

class SalePolicy
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

    public function view(User $user, Sale $sale): bool
    {
        if ($user->hasAnyRole(['admin_sucursal', 'cajero'])) {
            return true;
        }
        // Barbero solo ve ventas donde él fue el staff
        if ($user->hasRole('barbero')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');
            return $sale->staff_id === $staffId;
        }
        return false;
    }

    // Cajero y barbero crean ventas exclusivamente via PosPage
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero', 'barbero']);
    }

    public function update(User $user, Sale $_sale): bool
    {
        // Solo admins pueden editar una venta ya registrada
        return $user->hasRole('admin_sucursal');
    }

    public function delete(User $user, Sale $_sale): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function forceDelete(User $user, Sale $_sale): bool
    {
        return false;
    }

    public function forceDeleteAny(User $_user): bool
    {
        return false;
    }

    public function restore(User $user, Sale $_sale): bool
    {
        return false;
    }

    public function restoreAny(User $_user): bool
    {
        return false;
    }

    public function replicate(User $user, Sale $_sale): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }
}
