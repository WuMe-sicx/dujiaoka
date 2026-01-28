<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TopGoodsWidget extends ChartWidget
{
    protected ?string $heading = '热销商品 TOP5';

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
        $startTime = match ($this->filter) {
            'today' => Carbon::today(),
            'month' => Carbon::now()->subDays(30),
            default => Carbon::now()->subDays(7),
        };

        $topGoods = Order::query()
            ->where('orders.created_at', '>=', $startTime)
            ->where('orders.status', Order::STATUS_COMPLETED)
            ->select('orders.title', DB::raw('SUM(orders.buy_amount) as total_sold'), DB::raw('SUM(orders.actual_price) as total_revenue'))
            ->groupBy('orders.title')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $labels = $topGoods->pluck('title')->map(fn ($t) => mb_substr($t, 0, 8))->toArray();
        $soldData = $topGoods->pluck('total_sold')->map(fn ($v) => (int) $v)->toArray();
        $revenueData = $topGoods->pluck('total_revenue')->map(fn ($v) => round((float) $v, 2))->toArray();

        return [
            'datasets' => [
                [
                    'label' => '销量',
                    'data' => $soldData,
                    'backgroundColor' => [
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(139, 92, 246, 0.7)',
                        'rgba(168, 85, 247, 0.7)',
                        'rgba(192, 132, 252, 0.7)',
                        'rgba(196, 181, 253, 0.7)',
                    ],
                    'borderColor' => [
                        'rgba(99, 102, 241, 0.3)',
                        'rgba(139, 92, 246, 0.3)',
                        'rgba(168, 85, 247, 0.3)',
                        'rgba(192, 132, 252, 0.3)',
                        'rgba(196, 181, 253, 0.3)',
                    ],
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getContentHeight(): ?int
    {
        return 220;
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                indexAxis: 'y',
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                            font: { size: 10 },
                            stepSize: 1,
                        },
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                            font: { size: 10 },
                        },
                    },
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#e2e8f0',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 6,
                    },
                },
                animation: {
                    duration: 600,
                },
            }
        JS);
    }
}
