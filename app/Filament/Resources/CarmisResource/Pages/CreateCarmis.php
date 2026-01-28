<?php

namespace App\Filament\Resources\CarmisResource\Pages;

use App\Filament\Resources\CarmisResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCarmis extends CreateRecord
{
    protected static string $resource = CarmisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
