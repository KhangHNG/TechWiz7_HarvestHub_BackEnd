<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Model::withoutEvents(function () {
            $this->call([
                UserSeeder::class,
                MarketSeeder::class,
                FarmerSeeder::class,
                CategorySeeder::class,
                ProductSeeder::class,
                WishlistSeeder::class,
                FarmerFollowSeeder::class,
                OrderSeeder::class,
                OrderItemSeeder::class,
            ]);
        });
    }
}
