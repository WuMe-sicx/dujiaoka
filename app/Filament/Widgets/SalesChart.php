<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected ?string $heading = '销售趋势';

    protected ?string $description = '收入与完成订单';

    protected static ?int $sort = 2;

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

        $revenue = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', Order::STATUS_COMPLETED)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(actual_price) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $orderCounts = Order::query()
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', Order::STATUS_COMPLETED)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(id) as num'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('num', 'date')
            ->toArray();

        $allDates = array_unique(array_merge(array_keys($revenue), array_keys($orderCounts)));
        sort($allDates);

        // Format dates for display (MM-DD)
        $labels = array_map(fn ($d) => substr($d, 5), $allDates);

        $revenueData = [];
        $countData = [];
        foreach ($allDates as $date) {
            $revenueData[] = round((float) ($revenue[$date] ?? 0), 2);
            $countData[] = $orderCounts[$date] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => '销售额 (¥)',
                    'data' => $revenueData,
                    'backgroundColor' => 'rgba(99, 102, 241, 0.5)',
                    'borderColor' => 'rgba(129, 140, 248, 1)',
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'hoverBackgroundColor' => 'rgba(129, 140, 248, 0.8)',
                    'type' => 'bar',
                    'yAxisID' => 'y',
                    'order' => 2,
                ],
                [
                    'label' => '完成订单',
                    'data' => $countData,
                    'borderColor' => 'rgba(52, 211, 153, 1)',
                    'backgroundColor' => 'rgba(52, 211, 153, 0.08)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointBackgroundColor' => 'rgba(52, 211, 153, 1)',
                    'pointBorderColor' => 'rgba(16, 185, 129, 1)',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 8,
                    'pointHoverBackgroundColor' => '#6ee7b7',
                    'type' => 'line',
                    'yAxisID' => 'y1',
                    'order' => 1,
                    'borderWidth' => 3,
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
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                            font: { size: 10 },
                        },
                    },
                    y: {
                        position: 'left',
                        grid: {
                            color: 'rgba(148, 163, 184, 0.15)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: 'rgb(148, 163, 184)',
                            font: { size: 10 },
                        },
                        title: {
                            display: true,
                            text: '¥ 销售额',
                            color: 'rgb(148, 163, 184)',
                            font: { size: 10 },
                        },
                    },
                    y1: {
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: {
                            color: 'rgb(52, 211, 153)',
                            font: { size: 10 },
                            stepSize: 1,
                        },
                        title: {
                            display: true,
                            text: '订单数',
                            color: 'rgb(52, 211, 153)',
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
                            usePointStyle: true,
                            pointStyleWidth: 8,
                            padding: 12,
                        },
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                        titleColor: '#e2e8f0',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(99, 102, 241, 0.3)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        mode: 'index',
                        intersect: false,
                    },
                },
                animation: {
                    duration: 800,
                },
            }
        JS);
    }
}
