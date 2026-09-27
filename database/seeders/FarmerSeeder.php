<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $users = User::query()->where('role', 'FARMER')->orderBy('id')->get();
        $markets = Market::query()->orderBy('id')->get();

        $profiles = [
            ['Green Leaf Farm', 'Leafy vegetables harvested each morning and sold the same day at Ben Thanh Market.', 4.6],
            ['Riverside Orchard', 'Tree-ripened fruit picked in the morning and brought to Binh Tay Market.', 4.8],
            ['Red River Grains', 'Clean rice and grains packed to weight and sold at Dong Xuan Market.', 4.3],
            ['Morning Herb Garden', 'Herbs and spices cut the same day for Hom Market stalls.', 4.7],
            ['Han River Produce', 'Seasonal vegetables and fruit from farms outside Da Nang.', 4.4],
            ['Coastal Greens', 'Coastal vegetables and herbs delivered to Dam Market before dawn.', 4.5],
            ['Fort Fresh Farm', 'Mixed vegetables grown for the Crawford Market wholesale floor.', 4.2],
            ['Chandni Spice Garden', 'Dried spices and chili sorted for Khari Baoli traders.', 4.9],
            ['Kalasipalya Organics', 'Organic greens with a written care log, sold at KR Market.', 4.6],
            ['Koyambedu Harvest', 'Wholesale vegetables packed overnight for Koyambedu Market.', 4.1],
            ['Lindsay Street Dairy', 'Fresh milk and yogurt made for New Market stalls in Kolkata.', 4.5],
            ['Charminar Honey House', 'Raw honey and bee products bottled for Laad Bazaar.', 4.8],
        ];

        foreach ($profiles as $index => [$businessName, $description, $rating]) {
            $this->createAudited(Farmer::class, [
                'user_id' => $users[$index]->id,
                'market_id' => $markets[$index]->id,
                'business_name' => $businessName,
                'description' => $description,
                'rating' => $rating,
                'is_accepting_orders' => true,
                'cover_url' => '/seed/products/mat-ong-'.(($index % 2) + 1).'.jpg',
            ]);
        }
    }
}
