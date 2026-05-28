<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles si no existen
        $roles = ['super_admin', 'admin_sucursal', 'cajero', 'barbero'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Usuario admin principal ya existe (Gory), solo asignar rol
        $admin = User::find(1);
        if ($admin && !$admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}