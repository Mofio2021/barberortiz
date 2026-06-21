<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffConsumptionResource\Pages;
use App\Models\Staff;
use App\Models\StaffConsumption;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StaffConsumptionResource extends Resource
{
    protected static ?string $model = StaffConsumption::class;

    protected static ?string $navigationIcon   = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel  = 'Consumos de Barberos';
    protected static ?string $modelLabel       = 'Consumo';
    protected static ?string $pluralModelLabel = 'Consumos';
    protected static ?string $navigationGroup  = 'Comisiones';
    protected static ?int    $navigationSort   = 20;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

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
        $user = Auth::user();

        return $form->schema([
            Forms\Components\Section::make('Consumo del barbero')
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

                    Forms\Components\TextInput::make('description')
                        ->label('Descripción (qué consumió)')
                        ->required()
                        ->placeholder('Ej: Gaseosa, almuerzo, producto...'),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto (Bs)')
                        ->numeric()
                        ->required()
                        ->minValue(0.01),

                    Forms\Components\DatePicker::make('consumed_at')
                        ->label('Fecha')
                        ->required()
                        ->default(today()),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->nullable(),

                    Forms\Components\Hidden::make('branch_id')
                        ->default(fn () => $user instanceof User ? $user->branch_id : null),

                    Forms\Components\Hidden::make('registered_by')
                        ->default(fn () => Auth::id()),
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

                Tables\Columns\TextColumn::make('description')
                    ->label('Consumo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state) => 'Bs ' . number_format($state, 2))
                    ->sortable(),

                Tables\Columns\TextColumn::make('consumed_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('commission_payment_id')
                    ->label('Estado')
                    ->formatStateUsing(fn ($state) => $state ? 'Descontado' : 'Pendiente')
                    ->colors([
                        'success' => fn ($state) => $state !== null,
                        'warning' => fn ($state) => $state === null,
                    ]),
            ])
            ->defaultSort('consumed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('staff_id')
                    ->label('Barbero')
                    ->relationship('staff', 'name'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStaffConsumptions::route('/'),
            'create' => Pages\CreateStaffConsumption::route('/create'),
            'edit'   => Pages\EditStaffConsumption::route('/{record}/edit'),
        ];
    }
}
