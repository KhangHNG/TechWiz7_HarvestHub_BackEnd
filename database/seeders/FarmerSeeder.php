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
            ['Green Leaf Farm', 'Morning-cut leafy vegetables packed for Ben Thanh Market the same day.', 4.6],
            ['Riverside Orchard', 'Tree-ripened fruit picked at dawn and sold at Binh Tay Market.', 4.8],
            ['Red River Grains', 'Milled rice, glutinous rice, and dried beans weighed for Dong Xuan Market.', 4.3],
            ['Morning Herb Garden', 'Herbs cut the same morning for the stalls at Hom Market.', 4.7],
            ['Han River Produce', 'Seasonal fruit and vegetables from farms outside Da Nang, sold at Han Market.', 4.4],
            ['Coastal Greens', 'Cabbage, cauliflower, and garden greens delivered to Dam Market before dawn.', 4.5],
            ['Fort Fresh Farm', 'Vegetables and cultivated mushrooms grown for the Crawford Market floor in Mumbai.', 4.2],
            ['Chandni Spice Garden', 'Dried chili, turmeric, garlic, and whole spices sorted for Khari Baoli.', 4.9],
            ['Kalasipalya Organics', 'Organic greens and roots with a written harvest log, sold at KR Market.', 4.6],
            ['Koyambedu Harvest', 'Wholesale vegetables packed overnight for Koyambedu Market in Chennai.', 4.1],
            ['Lindsay Street Dairy', 'Fresh milk, yogurt, paneer, and butter made for New Market in Kolkata.', 4.5],
            ['Charminar Honey House', 'Raw honey, bee pollen, and beeswax bottled for Laad Bazaar in Hyderabad.', 4.8],
        ];

        foreach ($profiles as $index => [$businessName, $description, $rating]) {
            $this->createAudited(Farmer::class, [
                'user_id' => $users[$index]->id,
                'market_id' => $markets[$index]->id,
                'business_name' => $businessName,
                'description' => $description,
                'rating' => $rating,
                'is_accepting_orders' => true,
                'cover_url' => null,
            ]);
        }
    }
}
