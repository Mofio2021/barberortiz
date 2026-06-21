<?php

namespace App\Filament\Resources\StaffConsumptionResource\Pages;

use App\Filament\Resources\StaffConsumptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStaffConsumptions extends ListRecords
{
    protected static string $resource = StaffConsumptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
