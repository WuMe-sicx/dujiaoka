<?php

namespace App\Filament\Resources\GoodsGroupResource\Pages;

use App\Filament\Resources\GoodsGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGoodsGroup extends EditRecord
{
    protected static string $resource = GoodsGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
