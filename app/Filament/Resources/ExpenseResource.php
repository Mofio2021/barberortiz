<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon   = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel  = 'Egresos';
    protected static ?string $modelLabel       = 'Egreso';
    protected static ?string $pluralModelLabel = 'Egresos';
    protected static ?string $navigationGroup  = 'Operaciones';
    protected static ?int    $navigationSort   = 20;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'cajero', 'barbero']);
    }

    // Barbero solo ve los egresos registrados por él mismo
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
            // user_id se auto-asigna al usuario autenticado
            Forms\Components\Hidden::make('user_id')
                ->default(fn (): int => Auth::id() ?? 0),

            Forms\Components\Section::make('Datos del egreso')
                ->schema([
                    Forms\Components\Select::make('category')
                        ->label('Categoría')
                        ->options([
                            'insumos'   => 'Insumos / Productos',
                            'servicios' => 'Servicios (agua, luz, internet)',
                            'alquiler'  => 'Alquiler / Local',
                            'salario'   => 'Salario / Nómina',
                            'equipo'    => 'Equipo / Herramientas',
                            'marketing' => 'Marketing / Publicidad',
                            'otros'     => 'Otros',
                        ])
                        ->required()
                        ->searchable(),

                    Forms\Components\DatePicker::make('expense_date')
                        ->label('Fecha del egreso')
                        ->required()
                        ->default(today())
                        ->displayFormat('d/m/Y'),

                    Forms\Components\TextInput::make('description')
                        ->label('Descripción')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('amount')
                        ->label('Monto (Bs)')
                        ->numeric()
                        ->required()
                        ->prefix('Bs'),

                    Forms\Components\Select::make('payment_method')
                        ->label('Método de pago')
                        ->options([
                            'cash'     => 'Efectivo',
                            'transfer' => 'Transferencia',
                            'card'     => 'Tarjeta',
                        ])
                        ->required()
                        ->default('cash'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Notas adicionales')
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
                // Descripción + categoría apiladas (mobile-first)
                Tables\Columns\TextColumn::make('description')
                    ->label('Descripción / Categoría')
                    ->searchable()
                    ->limit(40)
                    ->description(fn (Expense $record): string => $record->category ?? ''),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Monto')
                    ->formatStateUsing(fn ($state): string => 'Bs ' . number_format((float) $state, 2))
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash'     => 'success',
                        'transfer' => 'warning',
                        'card'     => 'primary',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash'     => 'Efectivo',
                        'transfer' => 'Transfer.',
                        'card'     => 'Tarjeta',
                        default    => $state,
                    }),

                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                // Solo visible para admins/cajero
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expense_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'insumos'   => 'Insumos',
                        'servicios' => 'Servicios',
                        'alquiler'  => 'Alquiler',
                        'salario'   => 'Salario',
                        'equipo'    => 'Equipo',
                        'marketing' => 'Marketing',
                        'otros'     => 'Otros',
                    ]),

                Tables\Filters\Filter::make('este_mes')
                    ->label('Este mes')
                    ->query(fn (Builder $query): Builder =>
                        $query->whereMonth('expense_date', now()->month)
                              ->whereYear('expense_date', now()->year)
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
            'index'  => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit'   => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
