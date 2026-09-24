<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i));

        $revenue = $days->map(function ($day) {
            return Order::whereDate('created_at', $day->toDateString())->sum('total_price');
        });

        return [
            'datasets' => [
                [
                    'label' => 'Doanh thu (đ)',
                    'data' => $revenue->toArray(),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.2)',
                ],
            ],
            'labels' => $days->map(fn ($d) => $d->format('d/m'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
