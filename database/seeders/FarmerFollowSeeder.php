<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\FarmerFollow;
use App\Models\User;
use Illuminate\Database\Seeder;

class FarmerFollowSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $customers = User::query()->where('role', 'CUSTOMER')->orderBy('id')->get();
        $farmers = Farmer::query()->orderBy('id')->get();

        foreach ($customers as $customerIndex => $customer) {
            for ($slot = 0; $slot < 2; $slot++) {
                $farmer = $farmers[($customerIndex + $slot) % $farmers->count()];

                $this->createAudited(FarmerFollow::class, [
                    'customer_id' => $customer->id,
                    'farmer_id' => $farmer->id,
                ]);
            }
        }
    }
}
