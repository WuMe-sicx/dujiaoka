<?php

namespace App\Filament\Resources\EmailtplResource\Pages;

use App\Filament\Resources\EmailtplResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmailtpl extends EditRecord
{
    protected static string $resource = EmailtplResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
