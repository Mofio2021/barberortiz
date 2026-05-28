<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon   = 'heroicon-o-tag';
    protected static ?string $navigationLabel  = 'Categorías';
    protected static ?string $modelLabel       = 'Categoría';
    protected static ?string $pluralModelLabel = 'Categorías';
    protected static ?string $navigationGroup  = 'Configuración';
    protected static ?int    $navigationSort   = 30;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre de Categoría')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('type')
                ->label('Tipo')
                ->options([
                    'service' => 'Servicio',
                    'product' => 'Producto',
                ])
                ->required()
                ->default('service'),

            Forms\Components\ColorPicker::make('color')
                ->label('Color de etiqueta')
                ->default('#f59e0b'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'service' ? 'info' : 'warning')
                    ->formatStateUsing(fn (string $state): string => $state === 'service' ? 'Servicio' : 'Producto'),

                Tables\Columns\ColorColumn::make('color')
                    ->label('Color'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'service' => 'Servicios',
                        'product' => 'Productos',
                    ]),
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
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
