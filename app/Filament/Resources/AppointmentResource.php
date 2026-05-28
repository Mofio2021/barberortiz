<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon   = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel  = 'Citas';
    protected static ?string $modelLabel       = 'Cita';
    protected static ?string $pluralModelLabel = 'Citas';
    protected static ?string $navigationGroup  = 'Gestión';
    protected static ?int    $navigationSort   = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }

    // Barbero solo ve sus propias citas (vinculadas a su registro Staff)
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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos de la cita')
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label('Cliente')
                        ->options(fn (): array => Customer::orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('staff_id')
                        ->label('Barbero')
                        ->options(fn (): array => Staff::where('status', 'active')->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DateTimePicker::make('start_at')
                        ->label('Fecha y hora')
                        ->required()
                        ->seconds(false)
                        ->minutesStep(15)
                        ->columnSpanFull(),

                    Forms\Components\Select::make('service_id')
                        ->label('Servicio')
                        ->options(fn (): array => Service::active()->orderBy('name')->pluck('name', 'id')->toArray())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options([
                            'pendiente'  => 'Pendiente',
                            'confirmada' => 'Confirmada',
                            'completada' => 'Completada',
                            'cancelada'  => 'Cancelada',
                        ])
                        ->default('pendiente')
                        ->required(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Columna principal apilada: cliente + servicio/barbero debajo (mobile-first)
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente / Servicio')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Appointment $record): string =>
                        ($record->service?->name ?? '—') . '  ·  ' . ($record->staff?->name ?? '—')
                    ),

                // Fecha: dato crítico siempre visible
                Tables\Columns\TextColumn::make('start_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                // Estado con badge de color
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'confirmada',
                        'primary' => 'completada',
                        'danger'  => 'cancelada',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->defaultSort('start_at', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'pendiente'  => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'completada' => 'Completada',
                        'cancelada'  => 'Cancelada',
                    ]),

                Tables\Filters\SelectFilter::make('staff_id')
                    ->label('Barbero')
                    ->relationship('staff', 'name'),

                Tables\Filters\Filter::make('hoy')
                    ->label('Solo hoy')
                    ->query(fn (Builder $query): Builder => $query->whereDate('start_at', today())),
            ])
            ->actions([
                // Botón grande de acción rápida "Confirmar" — diseñado para pulgar móvil
                Tables\Actions\Action::make('confirmar')
                    ->label('Confirmar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->size('lg')
                    ->visible(fn (Appointment $record): bool => $record->status === 'pendiente')
                    ->action(fn (Appointment $record): bool => $record->update(['status' => 'confirmada'])),

                Tables\Actions\EditAction::make()->label('')->size('lg'),
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
            'index'  => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit'   => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
