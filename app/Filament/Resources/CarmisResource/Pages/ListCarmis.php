<?php

namespace App\Filament\Resources\CarmisResource\Pages;

use App\Filament\Resources\CarmisResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCarmis extends ListRecords
{
    protected static string $resource = CarmisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('import')
                ->label('导入卡密')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(fn () => route('filament.admin.pages.import-carmis'))
                ->color('success'),
        ];
    }
}
