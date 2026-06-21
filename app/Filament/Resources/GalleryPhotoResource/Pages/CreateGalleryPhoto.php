<?php

namespace App\Filament\Resources\GalleryPhotoResource\Pages;

use App\Filament\Resources\GalleryPhotoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryPhoto extends CreateRecord
{
    protected static string $resource = GalleryPhotoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
