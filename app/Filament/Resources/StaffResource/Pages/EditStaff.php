<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

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
        // password se deja vacío: el usuario solo escribe si quiere cambiarla

        return $data;
    }

    // Actualiza el User vinculado antes de guardar el Staff
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $staff = $this->record->load('user');

        if ($staff->user) {
            $userUpdate = [
                'name'      => $data['name'],
                'email'     => $data['email'],
                'branch_id' => $data['branch_id'],
                'phone'     => $data['phone'] ?? null,
                'role'      => $data['user_role'],
            ];

            // Solo actualiza contraseña si se escribió algo
            if (filled($data['password'] ?? null)) {
                $userUpdate['password'] = Hash::make($data['password']);
            }

            $staff->user->update($userUpdate);

            // Sincroniza el rol Spatie (reemplaza el anterior)
            $staff->user->syncRoles([$data['user_role']]);
        }

        // Limpia campos que no existen en la tabla staff
        unset($data['email'], $data['password'], $data['user_role']);

        return $data;
    }
}
