<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // admin_sucursal no ve el campo branch_id en el form → lo asignamos aquí
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin_sucursal') && empty($data['branch_id'])) {
            $data['branch_id'] = $user->branch_id;
        }

        return $data;
    }
}
