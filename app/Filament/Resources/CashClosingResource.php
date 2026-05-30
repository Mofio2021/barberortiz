<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashClosingResource\Pages;
use App\Models\CashClosing;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CashClosingResource extends Resource
{
    protected static ?string $model = CashClosing::class;

    protected static ?string $navigationIcon   = 'heroicon-o-calculator';
    protected static ?string $navigationLabel  = 'Cierres de Caja';
    protected static ?string $modelLabel       = 'Cierre de Caja';
    protected static ?string $pluralModelLabel = 'Cierres de Caja';
    protected static ?string $navigationGroup  = 'Operaciones';
    protected static ?int    $navigationSort   = 30;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }

    // Barbero solo ve los cierres que él mismo generó
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user instanceof User && $user->hasRole('barbero')) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            // ─── Encabezado del cierre ────────────────────────────────
            Forms\Components\Section::make('Encabezado')
                ->schema([
                    Forms\Components\Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship('branch', 'name')
                        ->required()
                        ->default(fn () => Auth::user()?->branch_id)
                        ->searchable()
                        ->hiddenOn('edit'),

                    Forms\Components\DatePicker::make('closing_date')
                        ->label('Fecha de cierre')
                        ->required()
                        ->default(today())
                        ->displayFormat('d/m/Y'),

                    Forms\Components\Toggle::make('is_closed')
                        ->label('Cierre confirmado')
                        ->default(false),
                ])
                ->columns(3),

            // ─── Desglose de ventas por método de pago ────────────────
            Forms\Components\Section::make('Desglose de ventas')
                ->description('Valores calculados automáticamente al crear el cierre. Puedes ajustarlos si es necesario.')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Forms\Components\TextInput::make('total_sales')
                        ->label('Total ventas (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('cash_sales')
                        ->label('Ventas en Efectivo (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('qr_sales')
                        ->label('Ventas en QR (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('total_expenses')
                        ->label('Total egresos (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('total_commissions')
                        ->label('Comisiones (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('net_profit')
                        ->label('Utilidad neta (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(3),

            // ─── Saldo en Efectivo Real ───────────────────────────────
            Forms\Components\Section::make('Saldo en Efectivo')
                ->description('El saldo real es lo que debe haber físicamente en caja: Inicial + Efectivo cobrado − Egresos.')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\TextInput::make('initial_balance')
                        ->label('Saldo Inicial (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->default(0)
                        ->live(onBlur: true)
                        ->helperText('Dinero que había en caja antes de abrir el día.'),

                    Forms\Components\Placeholder::make('cash_balance_real')
                        ->label('Saldo en Efectivo Real (Bs)')
                        ->content(fn (Get $get): string =>
                            'Bs ' . number_format(
                                max(0,
                                    (float) ($get('initial_balance') ?? 0)
                                    + (float) ($get('cash_sales')      ?? 0)
                                    - (float) ($get('total_expenses')   ?? 0)
                                ),
                                2
                            )
                        ),

                    Forms\Components\TextInput::make('cash_counted')
                        ->label('Efectivo contado (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->helperText('Lo que contaste físicamente al cierre.'),

                    Forms\Components\TextInput::make('cash_difference')
                        ->label('Diferencia (Bs)')
                        ->numeric()
                        ->prefix('Bs')
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(2),

            Forms\Components\Textarea::make('notes')
                ->label('Observaciones')
                ->rows(2)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('closing_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->description(fn (CashClosing $record): string =>
                        $record->user?->name ?? ''
                    ),

                Tables\Columns\TextColumn::make('total_sales')
                    ->label('Ventas')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('cash_sales')
                    ->label('Efectivo')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->color('success'),

                Tables\Columns\TextColumn::make('qr_sales')
                    ->label('QR')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Egresos')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Utilidad neta')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->weight('bold')
                    ->color(fn ($state): string => (float) $state >= 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('cash_difference')
                    ->label('Diferencia')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->color(fn ($state): string => (float) $state === 0.0 ? 'success' : 'warning')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('is_closed')
                    ->label('Cerrado')
                    ->boolean(),
            ])
            ->defaultSort('closing_date', 'desc')
            ->filters([
                Tables\Filters\Filter::make('abiertos')
                    ->label('Solo abiertos')
                    ->query(fn (Builder $query): Builder => $query->where('is_closed', false)),

                Tables\Filters\Filter::make('este_mes')
                    ->label('Este mes')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereMonth('closing_date', now()->month)
                              ->whereYear('closing_date', now()->year)
                    ),
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
            'index'  => Pages\ListCashClosings::route('/'),
            'create' => Pages\CreateCashClosing::route('/create'),
            'edit'   => Pages\EditCashClosing::route('/{record}/edit'),
        ];
    }
}
