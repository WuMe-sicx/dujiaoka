<?php

namespace App\Filament\Resources\GoodsGroupResource\Pages;

use App\Filament\Resources\GoodsGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGoodsGroup extends CreateRecord
{
    protected static string $resource = GoodsGroupResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
