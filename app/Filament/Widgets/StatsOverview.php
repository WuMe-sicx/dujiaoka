<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today's metrics
        $todayOrders = Order::whereDate('created_at', $today)->count();
        $yesterdayOrders = Order::whereDate('created_at', $yesterday)->count();

        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('actual_price');
        $yesterdayRevenue = Order::whereDate('created_at', $yesterday)
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('actual_price');

        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        // Calculate trends
        $orderTrend = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1)
            : ($todayOrders > 0 ? 100 : 0);

        $revenueTrend = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : ($todayRevenue > 0 ? 100 : 0);

        return [
            Stat::make('今日订单', $todayOrders)
                ->description($orderTrend >= 0 ? "较昨日 +{$orderTrend}%" : "较昨日 {$orderTrend}%")
                ->descriptionIcon($orderTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($orderTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, $todayOrders]),

            Stat::make('今日收入', '¥' . number_format($todayRevenue, 2))
                ->description($revenueTrend >= 0 ? "较昨日 +{$revenueTrend}%" : "较昨日 {$revenueTrend}%")
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueTrend >= 0 ? 'success' : 'danger')
                ->chart([7, 3, 4, 5, 6, 3, 5]),

            Stat::make('待处理订单', $pendingOrders)
                ->description('需要处理')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),
        ];
    }
}
