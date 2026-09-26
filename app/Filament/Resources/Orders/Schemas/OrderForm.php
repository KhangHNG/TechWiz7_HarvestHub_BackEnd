<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class OrderForm
{
    /**
     * @return array<string, string>
     */
    public static function statusOptions(?string $current): array
    {
        $labels = [
            'CART' => 'Giỏ hàng',
            'PENDING' => 'Chờ xác nhận',
            'CONFIRMED' => 'Đã xác nhận',
            'READY_FOR_PICKUP' => 'Sẵn sàng lấy hàng',
            'COMPLETED' => 'Hoàn thành',
            'CANCELLED' => 'Đã hủy',
        ];

        $allowed = match ($current) {
            null => ['CART', 'PENDING'],
            'CART' => ['CART', 'PENDING', 'CANCELLED'],
            'PENDING' => ['PENDING', 'CONFIRMED', 'CANCELLED'],
            'CONFIRMED' => ['CONFIRMED', 'READY_FOR_PICKUP', 'CANCELLED'],
            'READY_FOR_PICKUP' => ['READY_FOR_PICKUP', 'COMPLETED', 'CANCELLED'],
            'COMPLETED' => ['COMPLETED'],
            'CANCELLED' => ['CANCELLED'],
            default => array_keys($labels),
        };

        return array_intersect_key($labels, array_flip($allowed));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function serviceData(array $data, bool $withItems): array
    {
        $payload = [
            'customer_id' => $data['customer_id'],
            'delivery_address' => $data['delivery_address'] ?? null,
            'status' => $data['status'] ?? 'CART',
        ];

        if ($withItems || $payload['status'] === 'CART') {
            $payload['farmer_id'] = null;
        } elseif (array_key_exists('farmer_id', $data)) {
            $payload['farmer_id'] = $data['farmer_id'] ?: null;
        }

        if (! $withItems) {
            return $payload;
        }

        $payload['items'] = collect($data['items'] ?? [])->map(function (array $item) {
            $product = Product::query()->findOrFail($item['product_id']);
            $quantity = (int) $item['quantity'];

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit_price' => $product->price,
                'quantity' => $quantity,
                'line_total' => (float) $product->price * $quantity,
            ];
        })->all();

        return $payload;
    }

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
                Select::make('farmer_id')
                    ->label('Nông dân')
                    ->relationship('farmer', 'business_name')
                    ->searchable()
                    ->preload()
                    ->hidden(fn (?Order $record): bool => $record === null || $record->status === 'CART')
                    ->required(fn (?Order $record): bool => $record !== null && $record->status !== 'CART')
                    ->disabled(fn (?Order $record): bool => $record !== null && $record->status !== 'CART')
                    ->dehydrated(fn (?Order $record): bool => $record !== null && $record->status !== 'CART'),
                Textarea::make('delivery_address')
                    ->label('Địa chỉ giao hàng')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Trạng thái')
                    ->options(fn (?Order $record): array => self::statusOptions($record?->status))
                    ->default('CART')
                    ->required(),
                Repeater::make('items')
                    ->label('Sản phẩm')
                    ->schema([
                        Select::make('product_id')
                            ->label('Sản phẩm')
                            ->options(fn (): array => Product::query()
                                ->with('farmer')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(function (Product $product): array {
                                    $farmer = $product->farmer?->business_name;
                                    $label = $farmer ? $product->name.' — '.$farmer : $product->name;

                                    return [$product->id => $label];
                                })
                                ->all())
                            ->getOptionLabelUsing(function ($value): ?string {
                                $product = Product::withTrashed()->with('farmer')->find($value);

                                if (! $product) {
                                    return null;
                                }

                                $farmer = $product->farmer?->business_name;

                                return $farmer ? $product->name.' — '.$farmer : $product->name;
                            })
                            ->searchable()
                            ->required(),
                        TextInput::make('quantity')
                            ->label('Số lượng')
                            ->numeric()
                            ->minValue(1)
                            ->required(),
                    ])
                    ->minItems(1)
                    ->defaultItems(1)
                    ->disabled(fn (?Order $record): bool => $record !== null && $record->status !== 'CART')
                    ->dehydrated(fn (?Order $record): bool => $record === null || $record->status === 'CART')
                    ->columnSpanFull(),
            ]);
    }
}
