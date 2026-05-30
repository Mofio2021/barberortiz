<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    // Roles canónicos del sistema — nunca eliminar sin revisar StaffResource
    public const ROLES = [
        'super_admin',
        'admin_sucursal',
        'cajero',
        'barbero',
    ];

    public function run(): void
    {
        // Limpiar caché de permisos antes de crear/actualizar roles
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::ROLES as $name) {
            Role::firstOrCreate(
                ['name'       => $name],
                ['guard_name' => 'web']
            );
        }

        $this->command->info('Roles creados/verificados: ' . implode(', ', self::ROLES));
    }
}
