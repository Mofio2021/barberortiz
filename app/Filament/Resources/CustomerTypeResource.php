<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerTypeResource\Pages;
use App\Models\CustomerType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CustomerTypeResource extends Resource
{
    protected static ?string $model = CustomerType::class;

    protected static ?string $navigationIcon   = 'heroicon-o-tag';
    protected static ?string $navigationLabel  = 'Tipos de Cliente';
    protected static ?string $modelLabel       = 'Tipo de cliente';
    protected static ?string $pluralModelLabel = 'Tipos de cliente';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 55;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del tipo')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(100)
                        ->placeholder('Ej: Tiktokero, VIP personal, Cortesía…'),

                    Forms\Components\TextInput::make('description')
                        ->label('Descripción')
                        ->nullable()
                        ->maxLength(255)
                        ->placeholder('Descripción breve para el equipo…'),

                    Forms\Components\TextInput::make('discount_percentage')
                        ->label('Descuento')
                        ->numeric()
                        ->required()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->helperText('0 = precio normal · 100 = servicio completamente gratis'),

                    Forms\Components\Select::make('cost_bearer')
                        ->label('¿Quién asume el costo?')
                        ->required()
                        ->options([
                            'business' => 'Negocio — el barbero cobra comisión normal',
                            'barber'   => 'Barbero — su comisión se reduce al mismo % que el descuento',
                        ])
                        ->default('business')
                        ->helperText('Define de qué bolsillo sale el costo del descuento.'),

                    Forms\Components\Select::make('color')
                        ->label('Color del badge en POS')
                        ->required()
                        ->options([
                            'amber'  => 'Ámbar / Dorado',
                            'purple' => 'Morado',
                            'blue'   => 'Azul',
                            'green'  => 'Verde',
                            'red'    => 'Rojo',
                            'gray'   => 'Gris',
                        ])
                        ->default('amber'),

                    Forms\Components\Toggle::make('affects_loyalty')
                        ->label('Acumula puntos de fidelidad')
                        ->default(true)
                        ->helperText('Desmarca para tipos de intercambio (ej. tiktokeros).'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo')
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
                    ->label('Tipo')
                    ->searchable()
                    ->sortable()
                    ->description(fn (CustomerType $r): string => $r->description ?? ''),

                Tables\Columns\TextColumn::make('discount_percentage')
                    ->label('Descuento')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($s) => $s . '%'),

                Tables\Columns\TextColumn::make('cost_bearer')
                    ->label('Costo asume')
                    ->badge()
                    ->color(fn ($s) => $s === 'business' ? 'info' : 'danger')
                    ->formatStateUsing(fn ($s) => $s === 'business' ? 'Negocio' : 'Barbero'),

                Tables\Columns\TextColumn::make('customers_count')
                    ->label('Clientes')
                    ->counts('customers')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('affects_loyalty')
                    ->label('Puntos')
                    ->boolean(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListCustomerTypes::route('/'),
            'create' => Pages\CreateCustomerType::route('/create'),
            'edit'   => Pages\EditCustomerType::route('/{record}/edit'),
        ];
    }
}
