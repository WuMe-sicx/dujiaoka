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

        $orderGroup = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->select('status', DB::raw('count(id) as num'))
            ->groupBy('status')
            ->pluck('num', 'status')
            ->toArray();

        $completed = $orderGroup[Order::STATUS_COMPLETED] ?? 0;
        $pending = ($orderGroup[Order::STATUS_PENDING] ?? 0)
            + ($orderGroup[Order::STATUS_PROCESSING] ?? 0)
            + ($orderGroup[Order::STATUS_WAIT_PAY] ?? 0);
        $failed = ($orderGroup[Order::STATUS_FAILURE] ?? 0)
            + ($orderGroup[Order::STATUS_ABNORMAL] ?? 0)
            + ($orderGroup[Order::STATUS_EXPIRED] ?? 0);

        return [
            'datasets' => [
                [
                    'data' => [$completed, $pending, $failed],
                    'backgroundColor' => [
                        'rgba(52, 211, 153, 0.75)',
                        'rgba(251, 191, 36, 0.75)',
                        'rgba(248, 113, 113, 0.75)',
                    ],
                    'borderColor' => [
                        'rgba(52, 211, 153, 0.3)',
                        'rgba(251, 191, 36, 0.3)',
                        'rgba(248, 113, 113, 0.3)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => ['已完成', '待处理', '失败/异常'],
        ];
    }

    protected function getType(): string
    {
        return 'polarArea';
    }

    protected function getContentHeight(): ?int
    {
        return 220;
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<JS
            {
                maintainAspectRatio: false,
                scales: {
                    r: {
                        display: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            circular: true,
                        },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                            backdropColor: 'transparent',
                            font: { size: 9 },
                        },
                        pointLabels: {
                            color: 'rgb(148, 163, 184)',
                            font: { size: 10 },
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgb(148, 163, 184)',
                            font: { size: 11 },
                            padding: 12,
                            usePointStyle: true,
                            pointStyleWidth: 8,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleColor: '#e2e8f0',
                        bodyColor: '#cbd5e1',
                        padding: 10,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round(context.parsed.r / total * 100) : 0;
                                return ' ' + context.label + ': ' + context.parsed.r + ' (' + pct + '%)';
                            }
                        }
                    },
                },
                animation: {
                    animateRotate: true,
                    duration: 600,
                },
            }
        JS);
    }
}
