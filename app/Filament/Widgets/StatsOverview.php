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
            Stat::make('Tổng sản phẩm', Product::count())
                ->description('Sản phẩm đang bán')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('success'),

            Stat::make('Tổng đơn hàng', Order::count())
                ->description('Tất cả đơn hàng')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('Tổng doanh thu', number_format(Order::sum('total_price')) . ' đ')
                ->description('Tổng giá trị đơn hàng')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Tổng nông dân', Farmer::count())
                ->description('Nông dân đang hoạt động')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
