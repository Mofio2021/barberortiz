<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon   = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel  = 'Sucursales';
    protected static ?string $modelLabel       = 'Sucursal';
    protected static ?string $pluralModelLabel = 'Sucursales';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 40;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la sucursal')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre de la Sucursal')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej. Sucursal Central / Equipetrol'),

                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono de Contacto')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\TextInput::make('address')
                        ->label('Dirección')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('¿Sucursal Activa?')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Branch $record): string => $record->address ?? ''),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit'   => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
