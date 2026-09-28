<?php

namespace App\Filament\Widgets;

use App\Models\Farmer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected function getStats(): array
    {
        return [
            Stat::make('Total products', Product::count())
                ->description('Products for sale')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Total orders', Order::count())
                ->description('All orders')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Total revenue', '$'.number_format(Order::query()->where('status', 'COMPLETED')->sum('total_price'), 2))
                ->description('Order completed')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Total farmers', Farmer::count())
                ->description('Active farmers')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
