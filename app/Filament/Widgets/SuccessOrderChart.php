<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SuccessOrderChart extends ChartWidget
{
    protected ?string $heading = '完成订单趋势';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    public ?string $filter = 'seven';

    protected function getFilters(): ?array
    {
        return [
            'today' => '今日',
            'seven' => '近7天',
            'month' => '近30天',
        ];
    }

    protected function getData(): array
    {
        $endTime = Carbon::now();
        $startTime = match ($this->filter) {
            'today' => Carbon::today(),
            'month' => Carbon::now()->subDays(30),
            default => Carbon::now()->subDays(7),
        };

        $orders = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', Order::STATUS_COMPLETED)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as num'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => '完成订单',
                    'data' => $orders->pluck('num')->toArray(),
                    'borderColor' => '#34d399',
                    'backgroundColor' => 'rgba(52, 211, 153, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                    'pointBackgroundColor' => '#34d399',
                    'pointBorderColor' => '#10b981',
                    'pointHoverBackgroundColor' => '#6ee7b7',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
            'labels' => $orders->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getContentHeight(): ?int
    {
        return 250;
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            color: 'rgb(51, 65, 85)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                        },
                    },
                    y: {
                        grid: {
                            color: 'rgb(51, 65, 85)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                        },
                    },
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'rgb(148, 163, 184)',
                        },
                    },
                },
            }
        JS);
    }
}
