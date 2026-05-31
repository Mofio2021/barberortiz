<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashRegisterResource\Pages;
use App\Models\CashRegister;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static ?string $navigationIcon   = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel  = 'Historial de Caja';
    protected static ?string $modelLabel       = 'Sesión de Caja';
    protected static ?string $pluralModelLabel = 'Historial de Caja';
    protected static ?string $navigationGroup  = 'Operaciones';
    protected static ?int    $navigationSort   = 30;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    // admin_sucursal solo ve su propia sucursal
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user instanceof User && $user->hasRole('admin_sucursal') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Sesión de Caja')
                ->schema([
                    Forms\Components\Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship('branch', 'name')
                        ->disabled(),

                    Forms\Components\Select::make('user_id')
                        ->label('Cajero / Operador')
                        ->relationship('user', 'name')
                        ->disabled(),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(['open' => 'Abierta', 'closed' => 'Cerrada'])
                        ->disabled(),

                    Forms\Components\DateTimePicker::make('opened_at')
                        ->label('Apertura')
                        ->disabled(),

                    Forms\Components\DateTimePicker::make('closed_at')
                        ->label('Cierre')
                        ->disabled(),
                ])
                ->columns(3),

            Forms\Components\Section::make('Desglose de Ventas')
                ->icon('heroicon-o-banknotes')
                ->schema([
                    Forms\Components\TextInput::make('opening_balance')
                        ->label('Saldo inicial (Bs)')
                        ->prefix('Bs')
                        ->disabled(),

                    Forms\Components\TextInput::make('total_cash_sales')
                        ->label('Ventas Efectivo (Bs)')
                        ->prefix('Bs')
                        ->disabled(),

                    Forms\Components\TextInput::make('total_qr_sales')
                        ->label('Ventas QR (Bs)')
                        ->prefix('Bs')
                        ->disabled(),

                    Forms\Components\TextInput::make('total_expenses')
                        ->label('Egresos (Bs)')
                        ->prefix('Bs')
                        ->disabled(),

                    Forms\Components\TextInput::make('closing_balance')
                        ->label('Efectivo contado (Bs)')
                        ->prefix('Bs')
                        ->disabled(fn (): bool => ! Auth::user()?->hasRole('super_admin')),

                    Forms\Components\Placeholder::make('saldo_esperado')
                        ->label('Saldo esperado (Bs)')
                        ->content(fn (CashRegister $record): string =>
                            'Bs ' . number_format($record->expected_cash, 2)
                        ),
                ])
                ->columns(3),

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
                Tables\Columns\TextColumn::make('opened_at')
                    ->label('Apertura / Cajero')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (CashRegister $r): string => $r->user?->name ?? '—'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'success' => 'open',
                        'gray'    => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open'   => 'Abierta',
                        'closed' => 'Cerrada',
                        default  => $state,
                    }),

                Tables\Columns\TextColumn::make('opening_balance')
                    ->label('Inicial')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_cash_sales')
                    ->label('Efectivo')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->color('success'),

                Tables\Columns\TextColumn::make('total_qr_sales')
                    ->label('QR')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_expenses')
                    ->label('Egresos')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->color('danger')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('closing_balance')
                    ->label('Contado')
                    ->formatStateUsing(fn ($state): string =>
                        $state !== null ? 'Bs ' . number_format((float) $state, 2) : '—'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->getStateUsing(fn (CashRegister $record): ?float =>
                        $record->closing_balance === null
                            ? null
                            : (float) $record->closing_balance
                              - ((float) $record->opening_balance
                                 + (float) $record->total_cash_sales
                                 - (float) $record->total_expenses)
                    )
                    ->placeholder('—')
                    ->formatStateUsing(fn ($state): string =>
                        'Bs ' . number_format((float) $state, 2)
                    )
                    ->description(fn ($state): ?string => match (true) {
                        $state === null     => null,
                        (float) $state === 0.0 => 'Correcto',
                        (float) $state < 0  => 'Faltante',
                        default             => 'Sobrante',
                    })
                    ->color(fn ($state): string => match (true) {
                        $state === null        => 'gray',
                        (float) $state === 0.0 => 'success',
                        (float) $state < 0     => 'danger',
                        default                => 'warning',
                    }),

                Tables\Columns\TextColumn::make('closed_at')
                    ->label('Cierre')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('opened_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(['open' => 'Abierta', 'closed' => 'Cerrada']),

                Tables\Filters\Filter::make('fecha')
                    ->label('Rango de apertura')
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
                            ->when($data['desde'] ?? null, fn ($q, $v) => $q->whereDate('opened_at', '>=', $v))
                            ->when($data['hasta'] ?? null, fn ($q, $v) => $q->whereDate('opened_at', '<=', $v))
                    )
                    ->indicateUsing(fn (array $data): ?string =>
                        ($data['desde'] ?? null) || ($data['hasta'] ?? null)
                            ? 'Apertura: ' . ($data['desde'] ?? '…') . ' → ' . ($data['hasta'] ?? '…')
                            : null
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('imprimir')
                    ->label('')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->tooltip('Imprimir ticket de cierre')
                    ->url(fn (CashRegister $record): string => route('ticket.cierre', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (CashRegister $record): bool => $record->status === 'closed'),

                Tables\Actions\ViewAction::make()->label(''),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->visible(fn (): bool => (bool) Auth::user()?->hasRole('super_admin')),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashRegisters::route('/'),
            'view'  => Pages\ViewCashRegister::route('/{record}'),
            'edit'  => Pages\EditCashRegister::route('/{record}/edit'),
        ];
    }
}
