<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\FarmerFollow;
use App\Models\User;
use Illuminate\Database\Seeder;

class FarmerFollowSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'CUSTOMER')->orderBy('id')->get();
        $farmers = Farmer::query()->orderBy('id')->get();

        foreach ($customers as $index => $customer) {
            FarmerFollow::create([
                'customer_id' => $customer->id,
                'farmer_id' => $farmers[$index % $farmers->count()]->id,
            ]);
        }
    }
}
