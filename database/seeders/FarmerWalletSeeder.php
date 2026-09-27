<?php

namespace Database\Seeders;

use App\Models\FarmerWalletTransaction;
use App\Models\Order;
use Illuminate\Database\Seeder;

class FarmerWalletSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()
            ->where('status', 'COMPLETED')
            ->where('payment_method', 'BANK_TRANSFER')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            FarmerWalletTransaction::query()->create([
                'farmer_id' => $order->farmer_id,
                'order_id' => $order->id,
                'amount' => $order->total_price,
                'type' => 'credit',
            ]);
        }
    }
}
