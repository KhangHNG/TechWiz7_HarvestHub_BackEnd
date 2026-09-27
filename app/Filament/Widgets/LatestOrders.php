<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Latest orders';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Order ID'),
                Tables\Columns\TextColumn::make('customer.full_name')->label('Customer'),
                Tables\Columns\TextColumn::make('farmer.business_name')->label('Farmer'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('total_price')->label('Total')->money('vnd'),
                Tables\Columns\TextColumn::make('created_at')->label('Created at')->dateTime('d/m/Y H:i'),
            ])
            ->paginated([5]);
    }
}
