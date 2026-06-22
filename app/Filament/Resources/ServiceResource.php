<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon   = 'heroicon-o-scissors';
    protected static ?string $navigationLabel  = 'Servicios';
    protected static ?string $modelLabel       = 'Servicio';
    protected static ?string $pluralModelLabel = 'Servicios';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 10;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    // super_admin ve todo; admin_sucursal ve globales + los de su sucursal
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if ($user instanceof User && $user->hasRole('admin_sucursal')) {
            $query->where(function (Builder $q) use ($user) {
                $q->whereNull('branch_id')
                  ->orWhere('branch_id', $user->branch_id);
            });
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $isSuperAdmin = Auth::user()?->hasRole('super_admin');

        return $form->schema([
            Forms\Components\Section::make('Sucursal')
                ->schema([
                    // Solo el super_admin elige sucursal.
                    // null = global (visible en todas las sucursales).
                    Forms\Components\Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship('branch', 'name')
                        ->nullable()
                        ->placeholder('Global (todas las sucursales)')
                        ->searchable()
                        ->helperText('Deja en blanco para que el servicio esté disponible en todas las sucursales.'),
                ])
                ->visible($isSuperAdmin)
                ->columns(1),

            Forms\Components\Section::make('Foto del servicio')
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Foto')
                        ->image()
                        ->disk('public')
                        ->directory('services')
                        ->imageResizeMode('cover')
                        ->imageCropAspectRatio('4:3')
                        ->imageResizeTargetWidth('800')
                        ->imageResizeTargetHeight('600')
                        ->nullable()
                        ->helperText('Aparece en la página web. Formato 4:3 recomendado (ej. 800×600). Si no subís foto se usa la imagen predeterminada del servicio.'),
                ])
                ->columns(1),

            Forms\Components\Section::make('Datos del servicio')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Servicio')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej. Corte Degradé / Perfilado de Barba'),

                    Forms\Components\TextInput::make('price')
                        ->label('Precio de Venta (Bs)')
                        ->numeric()
                        ->required()
                        ->prefix('Bs'),

                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duración (min)')
                        ->numeric()
                        ->required()
                        ->default(30)
                        ->suffix('min'),

                    Forms\Components\Select::make('commission_type')
                        ->label('Tipo de Comisión')
                        ->options([
                            'percentage' => 'Porcentaje (%)',
                            'fixed'      => 'Monto Fijo (Bs)',
                        ])
                        ->required()
                        ->default('percentage'),

                    Forms\Components\TextInput::make('commission_value')
                        ->label('Valor de Comisión')
                        ->numeric()
                        ->required()
                        ->default(50.00),

                    Forms\Components\Toggle::make('is_active')
                        ->label('¿Servicio Activo?')
                        ->default(true)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        $isSuperAdmin = Auth::user()?->hasRole('super_admin');

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn (Service $r) => asset('images/services/' . strtolower(
                        str_contains(strtolower($r->name), 'barba') && str_contains(strtolower($r->name), 'corte') ? 'corte-barba' :
                        (str_contains(strtolower($r->name), 'tinte')  ? 'tinte'  :
                        (str_contains(strtolower($r->name), 'lavado') ? 'lavado' :
                        (str_contains(strtolower($r->name), 'base')   ? 'base'   :
                        (str_contains(strtolower($r->name), 'corte')  ? 'corte'  :
                        (str_contains(strtolower($r->name), 'barba')  ? 'barba'  : 'corte')))))
                    ) . '.jpg')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Service $record): string => $record->category?->name ?? ''),

                // Columna de sucursal: "Global" cuando branch_id es null
                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->default('Global')
                    ->badge()
                    ->color(fn (?string $state): string => $state === null || $state === 'Global' ? 'gray' : 'amber')
                    ->visible($isSuperAdmin),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->formatStateUsing(fn ($state): string => "Bs {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_value')
                    ->label('Comisión')
                    ->formatStateUsing(
                        fn (Service $record, $state): string =>
                            $record->commission_type === 'percentage' ? "{$state}%" : "Bs {$state}"
                    ),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name'),

                // Filtro de sucursal solo para super_admin
                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->placeholder('Todas')
                    ->visible($isSuperAdmin),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
