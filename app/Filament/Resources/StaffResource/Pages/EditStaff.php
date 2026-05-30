<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use Database\Seeders\RoleSeeder;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EditStaff extends EditRecord
{
    protected static string $resource = StaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Pre-carga los datos del User vinculado en el formulario
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $staff = $this->record->load('user.roles');

        $data['email']     = $staff->user?->email ?? '';
        $data['user_role'] = $staff->user?->roles->first()?->name ?? 'barbero';
        // password y pin se dejan vacíos: se rellenan solo si el admin quiere cambiarlos

        return $data;
    }

    // Actualiza el User vinculado antes de guardar el Staff
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $roleName = $data['user_role'] ?? 'barbero';

        // Validar que el rol es uno de los permitidos
        if (! in_array($roleName, RoleSeeder::ROLES, true)) {
            $this->halt();
            Notification::make()
                ->title('Rol inválido')
                ->body("El rol «{$roleName}» no está permitido en el sistema.")
                ->danger()
                ->send();
            return $data;
        }

        // Garantizar que el rol exista en BD
        $role = Role::firstOrCreate(
            ['name'       => $roleName],
            ['guard_name' => 'web']
        );

        $staff = $this->record->load('user');

        if ($staff->user) {
            $userUpdate = [
                'name'      => $data['name'],
                'email'     => $data['email'],
                'branch_id' => $data['branch_id'],
                'phone'     => $data['phone'] ?? null,
                'role'      => $roleName,
            ];

            if (filled($data['password'] ?? null)) {
                $userUpdate['password'] = Hash::make($data['password']);
            }

            if (filled($data['pin'] ?? null)) {
                $userUpdate['pin'] = Hash::make((string) $data['pin']);
            }

            $staff->user->update($userUpdate);

            // Sincroniza usando el objeto Role (evita RoleDoesNotExist)
            $staff->user->syncRoles([$role]);
        }

        unset($data['email'], $data['password'], $data['user_role'], $data['pin']);

        return $data;
    }
}
