<?php

namespace App\Filament\Resources\LoyaltyRewardResource\Pages;

use App\Filament\Resources\LoyaltyRewardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoyaltyReward extends CreateRecord
{
    protected static string $resource = LoyaltyRewardResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
