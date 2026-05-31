<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

    // Barbero solo ve sus propias ventas y solo de hoy.
    // Cajero también queda restringido a ventas de hoy.
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user instanceof User && $user->hasRole('barbero')) {
            $staffId = Staff::where('user_id', $user->id)->value('id');
            $query->where('staff_id', $staffId ?? 0);
        }

        if ($user instanceof User && $user->hasAnyRole(['barbero', 'cajero'])) {
            $query->whereDate('created_at', today());
        }

        return $query;
    }

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
                        ->required()
                        ->live(),

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
                ])
                ->columns(2),

            Forms\Components\Section::make('Pago en Efectivo')
                ->description('Registra el monto recibido y el cambio a devolver.')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\TextInput::make('amount_paid')
                        ->label('Monto recibido (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->live(onBlur: true)
                        ->required()
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, Get $get): void {
                            if (! filled($state)) {
                                $component->state((float) ($get('total') ?? 0));
                            }
                        })
                        ->afterStateUpdated(function (Set $set, Get $get, $state): void {
                            $set('change_given', max(0, (float) $state - (float) ($get('total') ?? 0)));
                        }),

                    Forms\Components\TextInput::make('change_given')
                        ->label('Cambio a devolver (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(2)
                ->visible(fn (Get $get): bool => $get('payment_method') === 'cash'),

            Forms\Components\Section::make('Comprobante QR')
                ->description('Adjunta la foto del comprobante de pago QR.')
                ->icon('heroicon-o-qr-code')
                ->schema([
                    Forms\Components\FileUpload::make('qr_receipt_path')
                        ->label('Imagen del comprobante')
                        ->image()
                        ->disk('public')
                        ->directory(fn (): string => 'comprobantes/' . today()->format('Y-m-d'))
                        ->imagePreviewHeight('120')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->columnSpanFull(),
                ])
                ->visible(fn (Get $get): bool => $get('payment_method') === 'qr'),

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

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash'     => 'success',
                        'qr'       => 'info',
                        'transfer' => 'warning',
                        'card'     => 'primary',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'     => 'Efectivo',
                        'qr'       => 'QR',
                        'transfer' => 'Transfer.',
                        'card'     => 'Tarjeta',
                        default    => $state,
                    }),

                // Icono-enlace al comprobante QR (solo visible cuando existe)
                Tables\Columns\IconColumn::make('qr_receipt_path')
                    ->label('Comprobante')
                    ->icon(fn ($state): string => filled($state) ? 'heroicon-o-photo' : 'heroicon-o-minus-circle')
                    ->color(fn ($state): string => filled($state) ? 'success' : 'gray')
                    ->url(fn (Sale $record): ?string =>
                        filled($record->qr_receipt_path)
                            ? Storage::disk('public')->url($record->qr_receipt_path)
                            : null
                    )
                    ->openUrlInNewTab()
                    ->tooltip(fn (Sale $record): ?string =>
                        filled($record->qr_receipt_path) ? 'Ver comprobante' : null
                    ),

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
                // Filtro de rango de fechas — solo para administradores
                // Los campos DatePicker con default(today()) activan el filtro por defecto
                Tables\Filters\Filter::make('fecha')
                    ->label('Rango de fechas')
                    ->form([
                        Forms\Components\DatePicker::make('desde')
                            ->label('Desde')
                            ->displayFormat('d/m/Y')
                            ->default(today()),
                        Forms\Components\DatePicker::make('hasta')
                            ->label('Hasta')
                            ->displayFormat('d/m/Y')
                            ->default(today()),
                    ])
                    ->query(fn (Builder $query, array $data): Builder =>
                        $query
                            ->when($data['desde'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['hasta'] ?? null, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
                    )
                    ->indicateUsing(function (array $data): ?string {
                        if (! ($data['desde'] ?? null) && ! ($data['hasta'] ?? null)) {
                            return null;
                        }
                        return 'Fecha: ' . ($data['desde'] ?? '…') . ' → ' . ($data['hasta'] ?? '…');
                    })
                    ->visible(fn (): bool => (bool) Auth::user()?->hasAnyRole(['super_admin', 'admin_sucursal'])),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'cash'     => 'Efectivo',
                        'qr'       => 'QR',
                        'transfer' => 'Transferencia',
                        'card'     => 'Tarjeta',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('imprimir')
                    ->label('')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->tooltip('Imprimir ticket')
                    ->url(fn (Sale $record): string => route('ticket.venta', $record))
                    ->openUrlInNewTab(),
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