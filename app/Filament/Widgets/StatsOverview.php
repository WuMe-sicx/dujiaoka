<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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

        // Payment conversion rate
        $todayTotal = $todayOrders ?: 1;
        $todayPaid = Order::whereDate('created_at', $today)
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->count();
        $conversionRate = round(($todayPaid / $todayTotal) * 100, 1);

        $yesterdayTotal = $yesterdayOrders ?: 1;
        $yesterdayPaid = Order::whereDate('created_at', $yesterday)
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->count();
        $yesterdayConversion = round(($yesterdayPaid / $yesterdayTotal) * 100, 1);
        $conversionTrend = round($conversionRate - $yesterdayConversion, 1);

        // Calculate trends
        $orderTrend = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100, 1)
            : ($todayOrders > 0 ? 100 : 0);

        $revenueTrend = $yesterdayRevenue > 0
            ? round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1)
            : ($todayRevenue > 0 ? 100 : 0);

        // Sparkline data: last 7 days order counts
        $orderSparkline = Order::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as num'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('num')
            ->toArray();

        // Sparkline data: last 7 days revenue
        $revenueSparkline = Order::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->where('status', Order::STATUS_COMPLETED)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(actual_price) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total')
            ->map(fn ($v) => (float) $v)
            ->toArray();

        // Sparkline data: last 7 days pending
        $pendingSparkline = Order::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_PROCESSING])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as num'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('num')
            ->toArray();

        // Sparkline data: last 7 days conversion
        $conversionSparkline = [];
        $dailyTotals = Order::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
        $dailyPaid = Order::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as paid'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('paid', 'date')
            ->toArray();
        foreach ($dailyTotals as $date => $total) {
            $paid = $dailyPaid[$date] ?? 0;
            $conversionSparkline[] = $total > 0 ? round(($paid / $total) * 100) : 0;
        }

        return [
            Stat::make('今日订单', $todayOrders)
                ->description($orderTrend >= 0 ? "较昨日 +{$orderTrend}%" : "较昨日 {$orderTrend}%")
                ->descriptionIcon($orderTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('info')
                ->chart($orderSparkline ?: [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('今日收入', '¥' . number_format($todayRevenue, 2))
                ->description($revenueTrend >= 0 ? "较昨日 +{$revenueTrend}%" : "较昨日 {$revenueTrend}%")
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($revenueSparkline ?: [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('待处理订单', $pendingOrders)
                ->description($pendingOrders > 0 ? '需要处理' : '无待处理')
                ->descriptionIcon($pendingOrders > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->chart($pendingSparkline ?: [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('支付转化率', $conversionRate . '%')
                ->description($conversionTrend >= 0 ? "较昨日 +{$conversionTrend}%" : "较昨日 {$conversionTrend}%")
                ->descriptionIcon($conversionTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($conversionRate >= 70 ? 'success' : ($conversionRate >= 50 ? 'warning' : 'danger'))
                ->chart($conversionSparkline ?: [0, 0, 0, 0, 0, 0, 0]),
        ];
    }
}
