<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $customers = User::query()->where('role', 'CUSTOMER')->orderBy('id')->get();
        $farmers = Farmer::query()->orderBy('id')->get();
        $laterStatuses = ['PENDING', 'CONFIRMED', 'READY_FOR_PICKUP', 'COMPLETED', 'CANCELLED'];

        foreach ($customers as $index => $customer) {
            $farmer = $farmers[$index % $farmers->count()];

            $this->createAudited(Order::class, [
                'customer_id' => $customer->id,
                'farmer_id' => $farmer->id,
                'delivery_address' => $customer->address,
                'city' => $customer->city,
                'district' => $customer->district,
                'ward' => $customer->ward,
                'status' => 'CART',
                'payment_method' => 'COD',
                'total_price' => 0,
                'completed_at' => null,
            ]);

            $status = $laterStatuses[$index % count($laterStatuses)];
            $completedAt = null;

            if ($status === 'COMPLETED') {
                $day = ($index % now()->daysInMonth) + 1;
                $completedAt = now()->startOfMonth()->addDays($day - 1)->setTime(9, 0);

                if ($completedAt->isFuture()) {
                    $completedAt = now()->subHours($index + 1);
                }
            }

            $this->createAudited(Order::class, [
                'customer_id' => $customer->id,
                'farmer_id' => $farmer->id,
                'delivery_address' => $customer->address,
                'city' => $customer->city,
                'district' => $customer->district,
                'ward' => $customer->ward,
                'status' => $status,
                'payment_method' => $index % 2 === 0 ? 'COD' : 'BANK_TRANSFER',
                'total_price' => 0,
                'completed_at' => $completedAt,
            ]);
        }
    }
}
