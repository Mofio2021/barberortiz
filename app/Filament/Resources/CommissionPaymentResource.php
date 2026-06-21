<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionPaymentResource\Pages;
use App\Models\CommissionPayment;
use App\Models\SaleItem;
use App\Models\Staff;
use App\Models\StaffConsumption;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CommissionPaymentResource extends Resource
{
    protected static ?string $model = CommissionPayment::class;

    protected static ?string $navigationIcon   = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel  = 'Planilla de Comisiones';
    protected static ?string $modelLabel       = 'Pago';
    protected static ?string $pluralModelLabel = 'Planilla';
    protected static ?string $navigationGroup  = 'Comisiones';
    protected static ?int    $navigationSort   = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['staff', 'branch']);
        $user  = Auth::user();

        if ($user instanceof User && $user->hasRole('admin_sucursal') && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $user = Auth::user();

        return $form->schema([
            Forms\Components\Section::make('Período de comisión')
                ->schema([
                    Forms\Components\Select::make('staff_id')
                        ->label('Barbero')
                        ->options(function () use ($user) {
                            $query = Staff::where('status', 'active');
                            if ($user instanceof User && $user->hasRole('admin_sucursal') && $user->branch_id) {
                                $query->where('branch_id', $user->branch_id);
                            }
                            return $query->pluck('name', 'id');
                        })
                        ->required()
                        ->searchable(),

                    Forms\Components\DatePicker::make('period_start')
                        ->label('Inicio del período')
                        ->required(),

                    Forms\Components\DatePicker::make('period_end')
                        ->label('Fin del período')
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->nullable(),

                    Forms\Components\Hidden::make('branch_id')
                        ->default(fn () => $user instanceof User ? $user->branch_id : null),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Barbero')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('period_start')
                    ->label('Período')
                    ->formatStateUsing(fn ($state, $record) =>
                        $record->period_start->format('d/m/Y') . ' — ' . $record->period_end->format('d/m/Y')
                    ),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Comisión bruta')
                    ->formatStateUsing(fn ($state) => 'Bs ' . number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('deductions')
                    ->label('Consumos')
                    ->formatStateUsing(fn ($state) => $state > 0 ? '- Bs ' . number_format($state, 2) : '—')
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('net_amount')
                    ->label('A pagar')
                    ->formatStateUsing(fn ($state) => 'Bs ' . number_format($state, 2))
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => 'Pendiente',
                        'paid'    => 'Pagado',
                        default   => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'paid',
                    ]),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Pagado el')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),
            ])
            ->defaultSort('period_end', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(['pending' => 'Pendiente', 'paid' => 'Pagado']),

                Tables\Filters\SelectFilter::make('staff_id')
                    ->label('Barbero')
                    ->relationship('staff', 'name'),
            ])
            ->actions([
                Action::make('recalcular')
                    ->label('Recalcular')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(fn ($record) => $record->isPending())
                    ->action(function ($record) {
                        $record->recalculate();
                        Notification::make()->title('Comisión recalculada')->success()->send();
                    }),

                Action::make('pagar')
                    ->label('Marcar como pagado')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar pago')
                    ->modalDescription(fn ($record) =>
                        "¿Pagaste Bs " . number_format($record->net_amount, 2) . " a {$record->staff->name}?"
                    )
                    ->action(function ($record) {
                        // Vincular consumos pendientes a este pago
                        StaffConsumption::where('staff_id', $record->staff_id)
                            ->whereBetween('consumed_at', [$record->period_start, $record->period_end])
                            ->whereNull('commission_payment_id')
                            ->update(['commission_payment_id' => $record->id]);

                        $record->update([
                            'status'  => 'paid',
                            'paid_at' => now(),
                            'paid_by' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title("Pago registrado — Bs " . number_format($record->net_amount, 2))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->isPending()),
            ])
            ->headerActions([
                Action::make('generar_planilla')
                    ->label('Generar planilla')
                    ->icon('heroicon-o-document-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\DatePicker::make('period_start')
                            ->label('Inicio del período')
                            ->required()
                            ->default(today()->startOfMonth()),

                        Forms\Components\DatePicker::make('period_end')
                            ->label('Fin del período')
                            ->required()
                            ->default(today()),
                    ])
                    ->action(function (array $data) {
                        $user = Auth::user();
                        $branchId = $user instanceof User ? $user->branch_id : null;

                        $query = Staff::where('status', 'active');
                        if ($branchId) $query->where('branch_id', $branchId);

                        $created = 0;
                        foreach ($query->get() as $staff) {
                            $exists = CommissionPayment::where('staff_id', $staff->id)
                                ->where('period_start', $data['period_start'])
                                ->where('period_end', $data['period_end'])
                                ->exists();

                            if ($exists) continue;

                            $gross = SaleItem::where('staff_id', $staff->id)
                                ->whereBetween('created_at', [
                                    $data['period_start'] . ' 00:00:00',
                                    $data['period_end']   . ' 23:59:59',
                                ])
                                ->sum('commission_amount');

                            $deductions = StaffConsumption::where('staff_id', $staff->id)
                                ->whereBetween('consumed_at', [$data['period_start'], $data['period_end']])
                                ->sum('amount');

                            CommissionPayment::create([
                                'staff_id'     => $staff->id,
                                'branch_id'    => $staff->branch_id,
                                'period_start' => $data['period_start'],
                                'period_end'   => $data['period_end'],
                                'gross_amount' => $gross,
                                'deductions'   => $deductions,
                                'net_amount'   => max(0, $gross - $deductions),
                                'status'       => 'pending',
                            ]);
                            $created++;
                        }

                        Notification::make()
                            ->title("Planilla generada: {$created} barberos")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCommissionPayments::route('/'),
            'create' => Pages\CreateCommissionPayment::route('/create'),
            'edit'   => Pages\EditCommissionPayment::route('/{record}/edit'),
        ];
    }
}
