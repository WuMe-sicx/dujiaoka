<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected ?string $heading = '销售收入';

    protected static ?int $sort = 3;

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
            ->where('status', '>', Order::STATUS_PENDING)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(actual_price) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => '销售额 (¥)',
                    'data' => $orders->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(99, 102, 241, 0.6)',
                    'borderColor' => '#818cf8',
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                    'hoverBackgroundColor' => 'rgba(129, 140, 248, 0.8)',
                ],
            ],
            'labels' => $orders->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
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
