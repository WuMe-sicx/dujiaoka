<?php

namespace App\Filament\Resources\GoodsResource\Pages;

use App\Filament\Resources\GoodsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGoods extends CreateRecord
{
    protected static string $resource = GoodsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
