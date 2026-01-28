<?php

namespace App\Filament\Pages;

use App\Models\Carmis;
use App\Models\Goods;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportCarmis extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.import-carmis';

    public ?array $data = [];

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-arrow-up-tray';
    }

    public static function getNavigationLabel(): string
    {
        return '导入卡密';
    }

    public static function getNavigationGroup(): ?string
    {
        return '销售管理';
    }

    public static function getNavigationSort(): ?int
    {
        return 5;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('goods_id')
                    ->label('Product')
                    ->options(
                        Goods::query()
                            ->where('type', Goods::AUTOMATIC_DELIVERY)
                            ->pluck('gd_name', 'id')
                    )
                    ->required()
                    ->searchable(),

                Forms\Components\Textarea::make('carmis_list')
                    ->label('Digital Keys (one per line)')
                    ->rows(15)
                    ->helperText('Enter digital keys, one per line'),

                Forms\Components\FileUpload::make('carmis_txt')
                    ->label('Or Upload TXT File')
                    ->disk('public')
                    ->acceptedFileTypes(['text/plain'])
                    ->maxSize(5120)
                    ->helperText('Upload a .txt file with keys, one per line'),

                Forms\Components\Toggle::make('remove_duplication')
                    ->label('Remove Duplicates')
                    ->default(false),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (empty($data['carmis_list']) && empty($data['carmis_txt'])) {
            Notification::make()
                ->title('Please provide keys via text or file upload')
                ->danger()
                ->send();
            return;
        }

        $carmisContent = '';

        if (! empty($data['carmis_txt'])) {
            $carmisContent = Storage::disk('public')->get($data['carmis_txt']);
        }

        if (! empty($data['carmis_list'])) {
            $carmisContent = $data['carmis_list'];
        }

        $carmisData = [];
        $tempList = explode(PHP_EOL, $carmisContent);

        foreach ($tempList as $val) {
            if (trim($val) != '') {
                $carmisData[] = [
                    'goods_id' => $data['goods_id'],
                    'carmi' => trim($val),
                    'status' => Carmis::STATUS_UNSOLD,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (! empty($data['remove_duplication'])) {
            $carmisData = collect($carmisData)->unique('carmi')->values()->all();
        }

        if (! empty($carmisData)) {
            Carmis::query()->insert($carmisData);
        }

        // Clean up uploaded file
        if (! empty($data['carmis_txt'])) {
            Storage::disk('public')->delete($data['carmis_txt']);
        }

        Notification::make()
            ->title('Successfully imported ' . count($carmisData) . ' keys')
            ->success()
            ->send();

        $this->form->fill();

        $this->redirect(\App\Filament\Resources\CarmisResource::getUrl('index'));
    }
}
