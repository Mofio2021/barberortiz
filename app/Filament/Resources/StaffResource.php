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

                    Forms\Components\Select::make('commission_frequency')
                        ->label('Frecuencia de pago')
                        ->options([
                            'daily'     => 'Diario',
                            'weekly'    => 'Semanal',
                            'biweekly'  => 'Quincenal',
                            'monthly'   => 'Mensual',
                        ])
                        ->required()
                        ->default('monthly'),
                ])
                ->columns(4),

            Forms\Components\Section::make('Cuenta de acceso al sistema')
                ->description('Credenciales que usará el staff para ingresar al panel.')
                ->icon('heroicon-o-key')
                ->schema([
                    Forms\Components\Select::make('user_role')
                        ->label('Rol en el sistema')
                        ->options(function (): array {
                            $roles = [
                                'barbero'        => 'Barbero',
                                'cajero'         => 'Cajero',
                                'admin_sucursal' => 'Administrador de Sucursal',
                            ];
                            $authUser = Auth::user();
                            if ($authUser instanceof User && $authUser->hasRole('super_admin')) {
                                $roles['super_admin'] = 'Super Admin (Propietario)';
                            }
                            return $roles;
                        })
                        ->required()
                        ->default('barbero'),

                    Forms\Components\TextInput::make('email')
                        ->label('Correo electrónico (login)')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        // En CREATE $record es null → valida único en toda la tabla.
                        // En EDIT $record es Staff → $record->user es el User vinculado
                        // cuyo ID se ignora, evitando el falso positivo de "email en uso".
                        ->unique(
                            table: 'users',
                            column: 'email',
                            ignorable: fn ($record) => $record?->user,
                        )
                        ->validationMessages([
                            'unique' => 'Este correo electrónico ya está registrado en el sistema.',
                        ]),

                    Forms\Components\TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->minLength(8)
                        ->helperText('Edición: deja en blanco para no cambiarla.'),

                    Forms\Components\TextInput::make('pin')
                        ->label('PIN de acceso rápido (4 dígitos)')
                        ->numeric()
                        ->minLength(4)
                        ->maxLength(4)
                        ->password()
                        ->revealable()
                        ->helperText('Opcional. Permite login rápido desde móvil sin contraseña larga.')
                        ->visible(fn (Forms\Get $get): bool =>
                            in_array($get('user_role'), ['barbero', 'cajero'])
                        )
                        ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Login')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                // Rol del sistema desde Spatie
                Tables\Columns\TextColumn::make('rol_sistema')
                    ->label('Rol')
                    ->getStateUsing(fn (Staff $record): string => match (
                        $record->user?->roles->first()?->name
                    ) {
                        'super_admin'    => 'Super Admin',
                        'admin_sucursal' => 'Administrador',
                        'cajero'         => 'Cajero',
                        'barbero'        => 'Barbero',
                        default          => 'Sin rol',
                    })
                    ->badge()
                    ->color(fn (Staff $record): string => match (
                        $record->user?->roles->first()?->name
                    ) {
                        'super_admin'    => 'danger',
                        'admin_sucursal' => 'warning',
                        'cajero'         => 'info',
                        default          => 'success',
                    }),

                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Pago')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'commission' => 'Comisión',
                        'salary'     => 'Sueldo',
                        'hybrid'     => 'Híbrido',
                        default      => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('commission_value')
                    ->label('Comisión')
                    ->formatStateUsing(
                        fn (Staff $record, $state): string =>
                            $record->commission_type === 'percentage'
                                ? "{$state}%"
                                : "Bs {$state}"
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

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
