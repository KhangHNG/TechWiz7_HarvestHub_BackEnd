<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Đơn hàng gần nhất';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Mã ĐH'),
                Tables\Columns\TextColumn::make('customer.full_name')->label('Khách hàng'),
                Tables\Columns\TextColumn::make('farmer.business_name')->label('Nông dân'),
                Tables\Columns\TextColumn::make('status')->label('Trạng thái')->badge(),
                Tables\Columns\TextColumn::make('total_price')->label('Tổng tiền')->money('vnd'),
                Tables\Columns\TextColumn::make('created_at')->label('Ngày tạo')->dateTime('d/m/Y H:i'),
            ])
            ->paginated([5]);
    }
}
