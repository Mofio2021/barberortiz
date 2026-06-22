<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon   = 'heroicon-o-users';
    protected static ?string $navigationLabel  = 'Clientes';
    protected static ?string $modelLabel       = 'Cliente';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $navigationGroup  = 'Gestión';
    protected static ?int    $navigationSort   = 20;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del cliente')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20),

                    Forms\Components\TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->nullable()
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Fecha de nacimiento')
                        ->nullable()
                        ->displayFormat('d/m/Y'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Tipo especial')
                ->schema([
                    Forms\Components\Select::make('customer_type_id')
                        ->label('Tipo de cliente')
                        ->options(fn () => CustomerType::active()->pluck('name', 'id'))
                        ->nullable()
                        ->placeholder('Cliente regular (sin tipo especial)')
                        ->helperText('Asigna un tipo especial para aplicar descuento automático en el POS.')
                        ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin_sucursal'])),
                ])
                ->visible(fn () => auth()->user()?->hasAnyRole(['super_admin', 'admin_sucursal'])),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Nombre + teléfono apilados: lo más útil en pantalla pequeña
                Tables\Columns\TextColumn::make('name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Customer $record): string => $record->phone ?? ''),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('customerType.name')
                    ->label('Tipo')
                    ->badge()
                    ->color('warning')
                    ->default('Regular')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_visits')
                    ->label('Visitas')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('loyalty_points')
                    ->label('Puntos')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('last_visit')
                    ->label('Última visita')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\Filter::make('cumpleanos_hoy')
                    ->label('Cumpleaños hoy')
                    ->query(fn ($query) => $query->whereRaw(
                        "DATE_FORMAT(birth_date, '%m-%d') = ?", [now()->format('m-d')]
                    )),
            ])
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
