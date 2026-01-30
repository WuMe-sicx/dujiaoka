<?php

namespace App\Filament\Resources\TopupOrderResource\Pages;

use App\Filament\Resources\TopupOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListTopupOrders extends ListRecords
{
    protected static string $resource = TopupOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
