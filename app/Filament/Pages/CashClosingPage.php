<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CashClosingPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.cash-closing-page';
    
    protected static ?string $title = 'Cierre de Caja Manual';
protected static ?string $navigationLabel = 'Cierre de Caja Manual';
}
