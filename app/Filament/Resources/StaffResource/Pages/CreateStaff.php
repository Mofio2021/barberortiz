<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $roleName = $data['user_role'] ?? 'barbero';

        // Validar que el rol es uno de los permitidos (evita inyección de roles)
        if (! in_array($roleName, RoleSeeder::ROLES, true)) {
            $this->halt();
            Notification::make()
                ->title('Rol inválido')
                ->body("El rol «{$roleName}» no está permitido en el sistema.")
                ->danger()
                ->send();
            return $data;
        }

        // Garantizar que el rol exista en BD; si falta, lo crea automáticamente
        // (evita RoleDoesNotExist si el seeder no se ejecutó en producción)
        $role = Role::firstOrCreate(
            ['name'       => $roleName],
            ['guard_name' => 'web']
        );

        // 1. Crea la cuenta de usuario del sistema
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'branch_id' => $data['branch_id'],
            'phone'     => $data['phone'] ?? null,
            'role'      => $roleName,
            'is_active' => true,
            'pin'       => filled($data['pin'] ?? null) ? Hash::make((string) $data['pin']) : null,
        ]);

        // 2. Asigna el rol Spatie usando el objeto Role (más seguro que string)
        $user->assignRole($role);

        // 3. Vincula el user_id al registro Staff
        $data['user_id'] = $user->id;

        // 4. Elimina los campos que no pertenecen al modelo Staff
        unset($data['email'], $data['password'], $data['user_role'], $data['pin']);

        return $data;
    }
}
