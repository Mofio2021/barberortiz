<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewCashRegister extends ViewRecord
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir')
                ->label('Imprimir ticket')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('ticket.cierre', $this->record))
                ->openUrlInNewTab()
                ->visible(fn (): bool => $this->record->status === 'closed'),
        ];
    }
}
