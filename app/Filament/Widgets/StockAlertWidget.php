<?php

namespace App\Filament\Widgets;

use App\Models\Carmis;
use App\Models\Goods;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class StockAlertWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '库存预警';

    public function table(Table $table): Table
    {
        $defaultThreshold = (int) dujiaoka_config_get('default_stock_threshold', 10);

        return $table
            ->query(
                Goods::query()
                    ->where('type', Goods::AUTOMATIC_DELIVERY)
                    ->where('is_open', 1)
                    ->withCount(['carmis as unsold_count' => function (Builder $query) {
                        $query->where('status', Carmis::STATUS_UNSOLD);
                    }])
                    ->having('unsold_count', '<=', DB::raw("COALESCE(stock_alert_threshold, {$defaultThreshold})"))
                    ->orderBy('unsold_count', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('gd_name')
                    ->label('商品名称')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('unsold_count')
                    ->label('当前库存')
                    ->color(fn (int $state): string => $state <= 5 ? 'danger' : 'warning')
                    ->badge(),
                Tables\Columns\TextColumn::make('stock_alert_threshold')
                    ->label('预警阈值')
                    ->default($defaultThreshold),
                Tables\Columns\TextColumn::make('group.gp_name')
                    ->label('分类'),
            ])
            ->actions([
                Tables\Actions\Action::make('addStock')
                    ->label('补货')
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn (Goods $record): string => route('filament.admin.resources.carmis.create', ['goods_id' => $record->id]))
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('库存充足')
            ->emptyStateDescription('所有商品库存均高于预警阈值')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false);
    }

    public static function canView(): bool
    {
        return true;
    }
}
