<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'CUSTOMER')->orderBy('id')->get();
        $farmers = Farmer::query()->orderBy('id')->get();
        $statuses = ['CART', 'PENDING', 'CONFIRMED', 'READY_FOR_PICKUP', 'COMPLETED', 'CANCELLED'];

        foreach ($customers as $index => $customer) {
            Order::create([
                'customer_id' => $customer->id,
                'farmer_id' => $farmers[$index % $farmers->count()]->id,
                'delivery_address' => $customer->address,
                'status' => $statuses[$index % count($statuses)],
                'total_price' => 0,
            ]);
        }
    }
}
