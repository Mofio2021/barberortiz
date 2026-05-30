<?php

namespace App\Filament\Resources\StaffResource\Pages;

use App\Filament\Resources\StaffResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // 1. Crea la cuenta de usuario del sistema
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'branch_id' => $data['branch_id'],
            'phone'     => $data['phone'] ?? null,
            'role'      => $data['user_role'],
            'is_active' => true,
        ]);

        // 2. Asigna el rol Spatie para que las Policies funcionen
        $user->assignRole($data['user_role']);

        // 3. Vincula el user_id al registro Staff
        $data['user_id'] = $user->id;

        // 4. Elimina los campos que no pertenecen al modelo Staff
        unset($data['email'], $data['password'], $data['user_role']);

        return $data;
    }
}
