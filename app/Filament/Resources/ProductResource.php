<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Precio Venta')
                    ->formatStateUsing(fn ($state) => $state . ' Bs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->badge()
                    ->color(fn ($state, $record) => $state <= $record->stock_min ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Filtrar por Categoría')
                    ->relationship('category', 'name'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}