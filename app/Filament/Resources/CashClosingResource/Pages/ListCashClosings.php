<?php

namespace App\Filament\Resources\CashClosingResource\Pages;

use App\Filament\Resources\CashClosingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCashClosings extends ListRecords
{
    protected static string $resource = CashClosingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
