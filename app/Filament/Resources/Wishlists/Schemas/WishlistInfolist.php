<?php

namespace App\Filament\Resources\Wishlists\Schemas;

use App\Models\Wishlist;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WishlistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('customer.full_name')->label('Khách hàng'),
                TextEntry::make('product.name')->label('Sản phẩm'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (?Wishlist $record): bool => (bool) $record?->trashed()),
            ]);
    }
}
