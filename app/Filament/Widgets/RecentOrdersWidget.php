<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = '最近订单';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->latest('created_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_sn')
                    ->label('订单号')
                    ->limit(12)
                    ->tooltip(fn ($record) => $record->order_sn)
                    ->copyable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('商品')
                    ->limit(10),
                Tables\Columns\TextColumn::make('actual_price')
                    ->label('金额')
                    ->money('CNY'),
                Tables\Columns\TextColumn::make('status')
                    ->label('状态')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        Order::STATUS_WAIT_PAY => '待支付',
                        Order::STATUS_PENDING => '待处理',
                        Order::STATUS_PROCESSING => '处理中',
                        Order::STATUS_COMPLETED => '已完成',
                        Order::STATUS_FAILURE => '失败',
                        Order::STATUS_ABNORMAL => '异常',
                        Order::STATUS_EXPIRED => '已过期',
                        default => '未知',
                    })
                    ->color(fn (int $state): string => match ($state) {
                        Order::STATUS_COMPLETED => 'success',
                        Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_WAIT_PAY => 'warning',
                        Order::STATUS_FAILURE, Order::STATUS_ABNORMAL => 'danger',
                        Order::STATUS_EXPIRED => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('时间')
                    ->since(),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc');
    }
}
