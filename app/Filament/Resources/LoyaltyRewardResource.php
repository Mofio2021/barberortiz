<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoyaltyRewardResource\Pages;
use App\Models\LoyaltyReward;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LoyaltyRewardResource extends Resource
{
    protected static ?string $model = LoyaltyReward::class;

    protected static ?string $navigationIcon   = 'heroicon-o-gift';
    protected static ?string $navigationLabel  = 'Premios de Fidelidad';
    protected static ?string $modelLabel       = 'Premio';
    protected static ?string $pluralModelLabel = 'Premios de Fidelidad';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 50;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Premio configurable')
                ->description('El cliente canjea sus puntos acumulados por este premio.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del premio')
                        ->placeholder('Ej: Corte gratis, Lavado de cabeza, Tinte gratis…')
                        ->required()
                        ->maxLength(100)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('description')
                        ->label('Descripción (opcional)')
                        ->placeholder('Ej: Incluye lavado y peinado básico')
                        ->maxLength(200)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('points_required')
                        ->label('Puntos necesarios para canjear')
                        ->helperText('El cliente acumula 1 punto por cada venta con al menos un servicio.')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->suffix('puntos'),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden de aparición')
                        ->helperText('Número menor aparece primero.')
                        ->numeric()
                        ->default(0),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Premio activo')
                        ->helperText('Los premios inactivos no se muestran en el POS.')
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
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('Premio')
                    ->searchable()
                    ->description(fn (LoyaltyReward $r) => $r->description),

                Tables\Columns\TextColumn::make('points_required')
                    ->label('Puntos')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => $state . ' pts')
                    ->sortable(),

                Tables\Columns\TextColumn::make('redemptions_count')
                    ->label('Canjes')
                    ->counts('redemptions')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLoyaltyRewards::route('/'),
            'create' => Pages\CreateLoyaltyReward::route('/create'),
            'edit'   => Pages\EditLoyaltyReward::route('/{record}/edit'),
        ];
    }
}
