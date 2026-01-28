<?php

namespace App\Filament\Resources\CarmisResource\Pages;

use App\Filament\Resources\CarmisResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCarmis extends EditRecord
{
    protected static string $resource = CarmisResource::class;

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
