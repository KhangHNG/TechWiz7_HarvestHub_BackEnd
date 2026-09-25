<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()->orderBy('id')->get();
        $products = Product::query()->orderBy('id')->get();

        foreach ($orders as $index => $order) {
            $lineItems = [
                $products[$index],
                $products[$index + $orders->count()],
            ];
            $total = 0;

            foreach ($lineItems as $offset => $product) {
                $quantity = $offset + 1;
                $lineTotal = $product->price * $quantity;
                $total += $lineTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);
            }

            $order->update(['total_price' => $total]);
        }
    }
}
