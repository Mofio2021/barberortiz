<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon   = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel  = 'Ventas';
    protected static ?string $modelLabel       = 'Venta';
    protected static ?string $pluralModelLabel = 'Ventas';
    protected static ?string $navigationGroup  = 'Operaciones';
    protected static ?int    $navigationSort   = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }

    // Barbero solo ve sus propias ventas
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user instanceof User && $user->hasRole('barbero')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');
            $query->where('staff_id', $staffId ?? 0);
        }

        return $query;
    }

    // Las ventas se crean desde el POS; el formulario es para revisión/ajuste admin
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Resumen de venta')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label('Cliente')
                        ->relationship('customer', 'name')
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Select::make('payment_method')
                        ->label('Método de pago')
                        ->options([
                            'cash'     => 'Efectivo',
                            'qr'       => 'QR',
                            'transfer' => 'Transferencia',
                            'card'     => 'Tarjeta',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('subtotal')
                        ->label('Subtotal (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled(),

                    Forms\Components\TextInput::make('discount')
                        ->label('Descuento (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->default(0),

                    Forms\Components\TextInput::make('total')
                        ->label('Total (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled(),

                    Forms\Components\TextInput::make('amount_paid')
                        ->label('Monto pagado (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled(),
                ])
                ->columns(2),

            Forms\Components\Textarea::make('notes')
                ->label('Notas')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna principal apilada: cliente + fecha debajo (mobile-first)
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente / Fecha')
                    ->searchable()
                    ->sortable()
                    ->default('Consumidor final')
                    ->description(fn (Sale $record): string =>
                        $record->created_at->format('d/m/Y H:i')
                        . ' · ' . ($record->staff?->name ?? '—')
                    ),

                // Monto: siempre visible, dato clave en POS móvil
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->sortable()
                    ->weight('bold'),

                // Método de pago con badge de color
                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Pago')
                    ->colors([
                        'success' => 'cash',
                        'info'    => 'qr',
                        'warning' => 'transfer',
                        'primary' => 'card',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'     => 'Efectivo',
                        'qr'       => 'QR',
                        'transfer' => 'Transfer.',
                        'card'     => 'Tarjeta',
                        default    => $state,
                    }),

                // Columnas secundarias: ocultas por defecto en pantallas pequeñas
                Tables\Columns\TextColumn::make('cashier.name')
                    ->label('Cajero')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Descuento')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'cash'     => 'Efectivo',
                        'qr'       => 'QR',
                        'transfer' => 'Transferencia',
                        'card'     => 'Tarjeta',
                    ]),

                Tables\Filters\Filter::make('hoy')
                    ->label('Solo hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()->label(''),
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
            'index'  => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit'   => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}
