<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffResource\Pages;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class StaffResource extends Resource
{
    protected static ?string $model = Staff::class;

    protected static ?string $navigationIcon   = 'heroicon-o-user-group';
    protected static ?string $navigationLabel  = 'Barberos / Staff';
    protected static ?string $modelLabel       = 'Personal';
    protected static ?string $pluralModelLabel = 'Personal';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 20;

    // Cajero: acceso de solo lectura (el policy bloquea create/update/delete)
    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del barbero')
                ->schema([
                    Forms\Components\Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship('branch', 'name')
                        ->required()
                        ->searchable(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'active'   => 'Activo',
                            'inactive' => 'Inactivo',
                        ])
                        ->required()
                        ->default('active'),
                ])
                ->columns(2),

            Forms\Components\Section::make('Esquema de pago')
                ->schema([
                    Forms\Components\Select::make('payment_type')
                        ->label('Tipo de pago')
                        ->options([
                            'commission' => 'Comisión',
                            'salary'     => 'Sueldo fijo',
                            'hybrid'     => 'Híbrido',
                        ])
                        ->required()
                        ->default('commission'),

                    Forms\Components\Select::make('commission_type')
                        ->label('Tipo de comisión')
                        ->options([
                            'percentage' => 'Porcentaje (%)',
                            'fixed'      => 'Monto fijo (Bs)',
                        ])
                        ->required()
                        ->default('percentage'),

                    Forms\Components\TextInput::make('commission_value')
                        ->label('Valor de comisión')
                        ->numeric()
                        ->required()
                        ->default(50.00),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre / Sucursal')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Staff $record): string => $record->branch?->name ?? ''),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Pago')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'commission' => 'Comisión',
                        'salary'     => 'Sueldo',
                        'hybrid'     => 'Híbrido',
                        default      => $state,
                    }),

                Tables\Columns\TextColumn::make('commission_value')
                    ->label('Comisión')
                    ->formatStateUsing(
                        fn (Staff $record, $state): string =>
                            $record->commission_type === 'percentage' ? "{$state}%" : "Bs {$state}"
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => $state === 'active' ? 'Activo' : 'Inactivo'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active'   => 'Activo',
                        'inactive' => 'Inactivo',
                    ]),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStaff::route('/'),
            'create' => Pages\CreateStaff::route('/create'),
            'edit'   => Pages\EditStaff::route('/{record}/edit'),
        ];
    }
}
