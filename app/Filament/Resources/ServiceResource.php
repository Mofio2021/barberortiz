<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon   = 'heroicon-o-scissors';
    protected static ?string $navigationLabel  = 'Servicios';
    protected static ?string $modelLabel       = 'Servicio';
    protected static ?string $pluralModelLabel = 'Servicios';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del servicio')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Servicio')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej. Corte Degradé / Perfilado de Barba'),

                    Forms\Components\TextInput::make('price')
                        ->label('Precio de Venta (Bs)')
                        ->numeric()
                        ->required()
                        ->prefix('Bs'),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duración (min)')
                        ->numeric()
                        ->required()
                        ->default(30)
                        ->suffix('min'),

                    Forms\Components\Select::make('commission_type')
                        ->label('Tipo de Comisión')
                        ->options([
                            'percentage' => 'Porcentaje (%)',
                            'fixed'      => 'Monto Fijo (Bs)',
                        ])
                        ->required()
                        ->default('percentage'),

                    Forms\Components\TextInput::make('commission_value')
                        ->label('Valor de Comisión')
                        ->numeric()
                        ->required()
                        ->default(50.00),

                    Forms\Components\Toggle::make('is_active')
                        ->label('¿Servicio Activo?')
                        ->default(true)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Service $record): string => $record->category?->name ?? ''),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state): string => "Bs {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_value')
                    ->label('Comisión')
                    ->formatStateUsing(
                        fn (Service $record, $state): string =>
                            $record->commission_type === 'percentage' ? "{$state}%" : "Bs {$state}"
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),
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
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
