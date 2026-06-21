<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryPhotoResource\Pages;
use App\Models\GalleryPhoto;
use App\Models\Staff;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class GalleryPhotoResource extends Resource
{
    protected static ?string $model = GalleryPhoto::class;

    protected static ?string $navigationIcon   = 'heroicon-o-photo';
    protected static ?string $navigationLabel  = 'Galería';
    protected static ?string $modelLabel       = 'Foto';
    protected static ?string $pluralModelLabel = 'Galería de fotos';
    protected static ?string $navigationGroup  = 'Gestión';
    protected static ?int    $navigationSort   = 20;

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal', 'barbero']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = Auth::user();

        if (! ($user instanceof User)) return $query;

        if ($user->hasRole('barbero')) {
            $staff = Staff::where('user_id', $user->id)->first();
            return $query->where('branch_id', $staff?->branch_id ?? 0)
                         ->where('staff_id', $staff?->id ?? 0);
        }

        if ($user->hasRole('admin_sucursal')) {
            $branchId = Staff::where('user_id', $user->id)->value('branch_id');
            return $query->where('branch_id', $branchId ?? 0);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $user  = Auth::user();
        $staff = Staff::where('user_id', $user?->id)->first();

        return $form->schema([
            Forms\Components\Section::make('Foto de galería')
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Foto')
                        ->image()
                        ->imageEditor()
                        ->disk('public')
                        ->directory('gallery')
                        ->maxSize(4096)
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('caption')
                        ->label('Descripción (opcional)')
                        ->maxLength(120)
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('branch_id')
                        ->default($staff?->branch_id),

                    Forms\Components\Hidden::make('staff_id')
                        ->default($staff?->id),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Visible en la web')
                        ->default(true),

                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden (menor = primero)')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->size(80),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Sucursal')
                    ->sortable(),

                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Barbero')
                    ->default('—'),

                Tables\Columns\TextColumn::make('caption')
                    ->label('Descripción')
                    ->limit(40)
                    ->default('—'),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Visible'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make()->label(''),
                Tables\Actions\DeleteAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListGalleryPhotos::route('/'),
            'create' => Pages\CreateGalleryPhoto::route('/create'),
            'edit'   => Pages\EditGalleryPhoto::route('/{record}/edit'),
        ];
    }
}
