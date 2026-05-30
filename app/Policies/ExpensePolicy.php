<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;

class ExpensePolicy
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

    public function view(User $user, Expense $expense): bool
    {
        if ($user->hasAnyRole(['admin_sucursal', 'cajero'])) {
            return true;
        }
        return $user->hasRole('barbero') && $expense->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin_sucursal', 'cajero', 'barbero']);
    }

    public function update(User $user, Expense $expense): bool
    {
        if ($user->hasAnyRole(['admin_sucursal', 'cajero'])) {
            return true;
        }
        // Barbero solo edita sus propios egresos del día en curso
        if ($user->hasRole('barbero')) {
            return $expense->user_id === $user->id
                && $expense->expense_date->isToday();
        }
        return false;
    }

    public function delete(User $user, Expense $_expense): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function forceDelete(User $user, Expense $_expense): bool
    {
        return false;
    }

    public function forceDeleteAny(User $_user): bool
    {
        return false;
    }

    public function restore(User $user, Expense $_expense): bool
    {
        return false;
    }

    public function restoreAny(User $_user): bool
    {
        return false;
    }

    public function replicate(User $user, Expense $_expense): bool
    {
        return $user->hasRole('admin_sucursal');
    }

    public function reorder(User $user): bool
    {
        return $user->hasRole('admin_sucursal');
    }
}
