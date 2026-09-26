<?php

namespace App\Filament\Resources\Wishlists\Schemas;

use App\Models\Wishlist;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class WishlistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->label('Khách hàng')
                    ->relationship(
                        'customer',
                        'full_name',
                        fn (Builder $query) => $query->where('role', 'CUSTOMER'),
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('product_id')
                    ->label('Sản phẩm')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->rules([
                        fn (Get $get, ?Wishlist $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $record): void {
                            $exists = Wishlist::query()
                                ->where('customer_id', $get('customer_id'))
                                ->where('product_id', $value)
                                ->when($record, fn (Builder $query) => $query->whereKeyNot($record->id))
                                ->exists();

                            if ($exists) {
                                $fail('Khách đã yêu thích sản phẩm này.');
                            }
                        },
                    ]),
            ]);
    }
}
