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
                    ->label('Customer'),
                TextEntry::make('farmer.business_name')
                    ->label('Farmer')
                    ->placeholder('-'),
                TextEntry::make('delivery_address')
                    ->label('Delivery address')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('city')
                    ->label('City')
                    ->placeholder('-'),
                TextEntry::make('district')
                    ->label('District')
                    ->placeholder('-'),
                TextEntry::make('ward')
                    ->label('Ward')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn (?string $state): string => OrderForm::statusOptions($state)[$state] ?? (string) $state),
                TextEntry::make('total_price')
                    ->label('Total')
                    ->money('usd')
                    ->placeholder('-'),
                TextEntry::make('completed_at')
                    ->label('Completed at')
                    ->dateTime()
                    ->placeholder('-'),
                RepeatableEntry::make('items')
                    ->label('Product')
                    ->schema([
                        TextEntry::make('product_name')->label('Product'),
                        TextEntry::make('quantity')->label('Quantity'),
                        TextEntry::make('unit_price')->label('Unit price'),
                        TextEntry::make('line_total')->label('Line total'),
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
