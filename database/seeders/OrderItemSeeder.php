<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $orders = Order::query()->orderBy('id')->get();

        foreach ($orders as $order) {
            $products = Product::query()
                ->where('farmer_id', $order->farmer_id)
                ->orderBy('id')
                ->get();

            $count = $products->count();
            $shift = $order->status === 'CART' ? 0 : 2;
            $start = ($order->customer_id + $shift) % $count;
            $chosen = [
                $products[$start],
                $products[($start + 1) % $count],
            ];
            $total = 0;

            foreach ($chosen as $offset => $product) {
                $quantity = $offset + 1;
                $lineTotal = (float) $product->price * $quantity;
                $total += $lineTotal;

                $this->createAudited(OrderItem::class, [
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                if (in_array($order->status, ['PENDING', 'CONFIRMED', 'READY_FOR_PICKUP', 'COMPLETED'], true)) {
                    $product->decrement('stock_qty', $quantity);
                }
            }

            $order->update(['total_price' => $total]);
        }
    }
}
