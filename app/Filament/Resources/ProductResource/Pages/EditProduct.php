<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

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

    // Garantiza que admin_sucursal no pueda mover un producto a otra sucursal
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = Auth::user();

        if ($user instanceof User && $user->hasRole('admin_sucursal')) {
            $data['branch_id'] = $user->branch_id;
        }

        return $data;
    }
}
