<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PayoutRateChart extends ChartWidget
{
    protected ?string $heading = '支付转化率';

    protected static ?int $sort = 2;

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

        $paid = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', '>', Order::STATUS_WAIT_PAY)
            ->count();

        $unpaid = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', '<=', Order::STATUS_WAIT_PAY)
            ->count();

        return [
            'datasets' => [
                [
                    'label' => '支付状态',
                    'data' => [$paid, $unpaid],
                    'backgroundColor' => [
                        '#34d399', // Paid - Emerald 400
                        '#f87171', // Unpaid - Red 400
                    ],
                    'borderColor' => [
                        '#10b981', // Emerald 500
                        '#ef4444', // Red 500
                    ],
                    'borderWidth' => 2,
                    'hoverBackgroundColor' => [
                        '#6ee7b7', // Emerald 300
                        '#fca5a5', // Red 300
                    ],
                ],
            ],
            'labels' => ['已支付', '未支付'],
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
