<?php

namespace App\Filament\Resources\EmailtplResource\Pages;

use App\Filament\Resources\EmailtplResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmailtpl extends CreateRecord
{
    protected static string $resource = EmailtplResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
