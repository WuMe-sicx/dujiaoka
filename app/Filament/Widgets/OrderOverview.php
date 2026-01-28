<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrderOverview extends ChartWidget
{
    protected ?string $heading = '订单状态分布';

    protected static ?int $sort = 1;

    public ?string $filter = 'seven';

    protected function getFilters(): ?array
    {
        return [
            'today' => '今日',
            'seven' => '近7天',
            'month' => '近30天',
            'year' => '近一年',
        ];
    }

    protected function getData(): array
    {
        $endTime = Carbon::now();
        $startTime = match ($this->filter) {
            'today' => Carbon::today(),
            'month' => Carbon::now()->subDays(30),
            'year' => Carbon::now()->subDays(365),
            default => Carbon::now()->subDays(7),
        };

        $orderGroup = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->select('status', DB::raw('count(id) as num'))
            ->groupBy('status')
            ->pluck('num', 'status')
            ->toArray();

        $completed = $orderGroup[Order::STATUS_COMPLETED] ?? 0;
        $pending = $orderGroup[Order::STATUS_PENDING] ?? 0;
        $processing = $orderGroup[Order::STATUS_PROCESSING] ?? 0;
        $failure = $orderGroup[Order::STATUS_FAILURE] ?? 0;
        $abnormal = $orderGroup[Order::STATUS_ABNORMAL] ?? 0;
        $expired = $orderGroup[Order::STATUS_EXPIRED] ?? 0;
        $waitPay = $orderGroup[Order::STATUS_WAIT_PAY] ?? 0;

        return [
            'datasets' => [
                [
                    'label' => '订单',
                    'data' => [$completed, $pending, $processing, $failure, $abnormal, $waitPay, $expired],
                    'backgroundColor' => [
                        '#34d399', // Completed - Emerald 400
                        '#fbbf24', // Pending - Amber 400
                        '#60a5fa', // Processing - Blue 400
                        '#f87171', // Failed - Red 400
                        '#fb923c', // Abnormal - Orange 400
                        '#94a3b8', // Wait Pay - Slate 400
                        '#64748b', // Expired - Slate 500
                    ],
                    'borderColor' => [
                        '#10b981', // Emerald 500
                        '#f59e0b', // Amber 500
                        '#3b82f6', // Blue 500
                        '#ef4444', // Red 500
                        '#f97316', // Orange 500
                        '#64748b', // Slate 500
                        '#475569', // Slate 600
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['已完成', '待处理', '处理中', '失败', '异常', '待支付', '已过期'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getContentHeight(): ?int
    {
        return 250;
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgb(148, 163, 184)',
                            font: {
                                size: 12
                            }
                        },
                        position: 'bottom'
                    },
                },
                cutout: '60%',
            }
        JS);
    }
}
