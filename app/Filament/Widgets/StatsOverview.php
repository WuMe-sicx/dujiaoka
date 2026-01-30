<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => '今日',
            'week' => '本周',
            'month' => '本月',
            'year' => '本年',
        ];
    }

    protected function getDateRange(): array
    {
        return match ($this->filter) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today(), Carbon::tomorrow()],
        };
    }

    protected function getPreviousDateRange(): array
    {
        return match ($this->filter) {
            'week' => [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()],
            'month' => [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()],
            'year' => [Carbon::now()->subYear()->startOfYear(), Carbon::now()->subYear()->endOfYear()],
            default => [Carbon::yesterday(), Carbon::today()],
        };
    }

    protected function getPeriodLabel(): string
    {
        return match ($this->filter) {
            'week' => '本周',
            'month' => '本月',
            'year' => '本年',
            default => '今日',
        };
    }

    protected function getCompareLabel(): string
    {
        return match ($this->filter) {
            'week' => '较上周',
            'month' => '较上月',
            'year' => '较去年',
            default => '较昨日',
        };
    }

    protected function getStats(): array
    {
        [$start, $end] = $this->getDateRange();
        [$prevStart, $prevEnd] = $this->getPreviousDateRange();
        $periodLabel = $this->getPeriodLabel();
        $compareLabel = $this->getCompareLabel();

        // Current period metrics
        $currentOrders = Order::whereBetween('created_at', [$start, $end])->count();
        $previousOrders = Order::whereBetween('created_at', [$prevStart, $prevEnd])->count();

        $currentRevenue = Order::whereBetween('created_at', [$start, $end])
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('actual_price');
        $previousRevenue = Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('actual_price');

        $pendingOrders = Order::where('status', Order::STATUS_PENDING)->count();

        // Payment conversion rate
        $currentTotal = $currentOrders ?: 1;
        $currentPaid = Order::whereBetween('created_at', [$start, $end])
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->count();
        $conversionRate = round(($currentPaid / $currentTotal) * 100, 1);

        $previousTotal = $previousOrders ?: 1;
        $previousPaid = Order::whereBetween('created_at', [$prevStart, $prevEnd])
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->count();
        $previousConversion = round(($previousPaid / $previousTotal) * 100, 1);
        $conversionTrend = round($conversionRate - $previousConversion, 1);

        // Calculate trends
        $orderTrend = $previousOrders > 0
            ? round((($currentOrders - $previousOrders) / $previousOrders) * 100, 1)
            : ($currentOrders > 0 ? 100 : 0);

        $revenueTrend = $previousRevenue > 0
            ? round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($currentRevenue > 0 ? 100 : 0);

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
            Stat::make("{$periodLabel}订单", $currentOrders)
                ->description($orderTrend >= 0 ? "{$compareLabel} +{$orderTrend}%" : "{$compareLabel} {$orderTrend}%")
                ->descriptionIcon($orderTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('info')
                ->chart($orderSparkline ?: [0, 0, 0, 0, 0, 0, 0]),

            Stat::make("{$periodLabel}收入", '¥' . number_format($currentRevenue, 2))
                ->description($revenueTrend >= 0 ? "{$compareLabel} +{$revenueTrend}%" : "{$compareLabel} {$revenueTrend}%")
                ->descriptionIcon($revenueTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color('success')
                ->chart($revenueSparkline ?: [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('待处理订单', $pendingOrders)
                ->description($pendingOrders > 0 ? '需要处理' : '无待处理')
                ->descriptionIcon($pendingOrders > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingOrders > 0 ? 'warning' : 'success')
                ->chart($pendingSparkline ?: [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('支付转化率', $conversionRate . '%')
                ->description($conversionTrend >= 0 ? "{$compareLabel} +{$conversionTrend}%" : "{$compareLabel} {$conversionTrend}%")
                ->descriptionIcon($conversionTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($conversionRate >= 70 ? 'success' : ($conversionRate >= 50 ? 'warning' : 'danger'))
                ->chart($conversionSparkline ?: [0, 0, 0, 0, 0, 0, 0]),
        ];
    }
}
