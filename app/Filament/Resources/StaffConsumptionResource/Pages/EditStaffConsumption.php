<?php

namespace App\Filament\Resources\StaffConsumptionResource\Pages;

use App\Filament\Resources\StaffConsumptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffConsumption extends EditRecord
{
    protected static string $resource = StaffConsumptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
