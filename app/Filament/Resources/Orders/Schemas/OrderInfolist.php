<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.full_name')
                    ->label('Khách hàng'),
                TextEntry::make('farmer.business_name')
                    ->label('Nông dân')
                    ->placeholder('-'),
                TextEntry::make('delivery_address')
                    ->label('Địa chỉ giao hàng')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('status')
                    ->label('Trạng thái')
                    ->formatStateUsing(fn (?string $state): string => OrderForm::statusOptions($state)[$state] ?? (string) $state),
                TextEntry::make('total_price')
                    ->label('Tổng tiền')
                    ->money('vnd')
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->label('Hoàn thành lúc')
                    ->dateTime()
                    ->placeholder('-'),
                RepeatableEntry::make('items')
                    ->label('Sản phẩm')
                    ->schema([
                        TextEntry::make('product_name')->label('Sản phẩm'),
                        TextEntry::make('quantity')->label('Số lượng'),
                        TextEntry::make('unit_price')->label('Đơn giá'),
                        TextEntry::make('line_total')->label('Thành tiền'),
                    ])
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (?Order $record): bool => (bool) $record?->trashed()),
            ]);
    }
}
