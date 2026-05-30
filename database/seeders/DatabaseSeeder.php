<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles canonicos del sistema (idempotente — seguro en produccion)
        $this->call(RoleSeeder::class);

        // Usuario admin principal ya existe (Gory), solo asignar rol si falta
        $admin = User::find(1);
        if ($admin && ! $admin->hasRole('super_admin')) {
            $admin->assignRole('super_admin');
        }
    }
}