<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon   = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel  = 'Productos';
    protected static ?string $modelLabel       = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 30;

    // Solo administradores gestionan el inventario; el personal usa el POS
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
                    Forms\Components\Select::make('branch_id')
                        ->label('Sucursal')
                        ->relationship('branch', 'name')
                        ->nullable()
                        ->placeholder('Global (todas las sucursales)')
                        ->searchable()
                        ->helperText('Deja en blanco para inventario compartido entre todas las sucursales.'),
                ])
                ->visible($isSuperAdmin)
                ->columns(1),

            Forms\Components\Section::make('Datos del producto')
                ->schema([
                    Forms\Components\Select::make('category_id')
                        ->label('Categoría')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del Producto')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Ej. Pomada Efecto Mate Suavecito'),

                    Forms\Components\TextInput::make('sku')
                        ->label('Código de Barra / SKU')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('price')
                        ->label('Precio de Venta (Bs)')
                        ->numeric()
                        ->required()
                        ->prefix('Bs'),

                    Forms\Components\TextInput::make('cost_price')
                        ->label('Precio de Costo (Bs)')
                        ->numeric()
                        ->required()
                        ->default(0.00)
                        ->prefix('Bs'),

                    Forms\Components\TextInput::make('stock')
                        ->label('Inventario Actual')
                        ->numeric()
                        ->required()
                        ->default(0),

                    Forms\Components\TextInput::make('stock_min')
                        ->label('Stock Mínimo (Alerta)')
                        ->numeric()
                        ->required()
                        ->default(5),

                    Forms\Components\Toggle::make('is_active')
                        ->label('¿Producto Activo?')
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
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => $record->sku ?? ''),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->default('Global')
                    ->badge()
                    ->color(fn (?string $state): string => $state === null || $state === 'Global' ? 'gray' : 'amber')
                    ->visible($isSuperAdmin),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio Venta')
                    ->formatStateUsing(fn ($state): string => "Bs {$state}")
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state, Product $record): string =>
                        $record->isOutOfStock() ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')
                    )
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filtrar por Categoría')
                    ->relationship('category', 'name'),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Sucursal')
                    ->relationship('branch', 'name')
                    ->placeholder('Todas')
                    ->visible($isSuperAdmin),

                Tables\Filters\Filter::make('bajo_stock')
                    ->label('Stock bajo / Agotado')
                    ->query(fn (Builder $q): Builder =>
                        $q->where('is_active', true)->whereColumn('stock', '<=', 'stock_min')
                    ),
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
