<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashClosingResource\Pages;
use App\Filament\Resources\CashClosingResource\RelationManagers;
use App\Models\CashClosing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CashClosingResource extends Resource
{
    protected static ?string $model = CashClosing::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Cierres de Caja';
protected static ?string $modelLabel = 'Cierre de Caja';
protected static ?string $pluralModelLabel = 'Cierres de Caja';

protected static ?string $title = 'Cierre de Caja Manual';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashClosings::route('/'),
            'create' => Pages\CreateCashClosing::route('/create'),
            'edit' => Pages\EditCashClosing::route('/{record}/edit'),
        ];
    }
}
