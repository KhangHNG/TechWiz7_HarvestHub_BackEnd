<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $farmers = Farmer::query()->orderBy('id')->get();
        $categories = Category::query()->orderBy('id')->get()->keyBy('name');

        $catalog = [
            'Vegetables' => ['Water spinach', 'Bok choy', 'Cherry tomato', 'Cucumber', 'Carrot', 'Spring onion', 'Lettuce', 'Choy sum', 'Sweet potato', 'Pumpkin'],
            'Fruits' => ['Hoa Loc mango', 'Watermelon', 'Ripe banana', 'Green pomelo', 'King orange', 'Guava', 'Longan', 'Dragon fruit', 'Papaya', 'Jackfruit'],
            'Grains' => ['ST25 rice', 'Jasmine rice', 'Glutinous rice', 'Wheat flour', 'Tapioca starch', 'Baby corn', 'Potato', 'Taro', 'Oats', 'Jobs tears'],
            'Dairy' => ['Fresh milk', 'Yogurt', 'Fresh cheese', 'Unsalted butter', 'Nut milk', 'Drinking yogurt', 'Condensed milk', 'Yogurt drink', 'Cheese stick', 'Fresh cream'],
            'Herbs' => ['Basil', 'Cilantro', 'Perilla', 'Betel leaf', 'Sawtooth coriander', 'Vietnamese coriander', 'Fish mint', 'Pennywort', 'Lemongrass', 'Ginger'],
            'Organic' => ['Mixed organic greens', 'Watercress', 'Malabar spinach', 'Sweet leaf', 'Amaranth', 'Kohlrabi', 'Cauliflower', 'Beetroot', 'Winter melon', 'Sponge gourd'],
            'Legumes' => ['Mung beans', 'Green beans', 'Red beans', 'Black beans', 'Peanuts', 'Cashews', 'Lotus seeds', 'Black sesame', 'Okra', 'Snow peas'],
            'Spices' => ['Birds eye chili', 'Turmeric', 'Garlic', 'Shallot', 'Black pepper', 'Cinnamon', 'Star anise', 'Clove', 'Lime', 'Calamansi'],
            'Mushrooms' => ['Oyster mushroom', 'King oyster mushroom', 'Enoki', 'Straw mushroom', 'Reishi', 'Cordyceps', 'Shiitake', 'Button mushroom', 'Snow fungus', 'King mushroom'],
            'Honey' => ['Coffee-blossom honey', 'Forest honey', 'Mint honey', 'Bee pollen', 'Beeswax', 'Artichoke tea', 'Coffee beans', 'Royal jelly', 'Longan-blossom honey', 'Lychee-blossom honey'],
        ];

        $images = [
            'Vegetables' => ['/seed/products/rau-cu-1.jpg', '/seed/products/rau-cu-2.jpg'],
            'Fruits' => ['/seed/products/trai-cay-1.jpg', '/seed/products/trai-cay-2.jpg'],
            'Grains' => ['/seed/products/ngu-coc-1.jpg', '/seed/products/ngu-coc-2.jpg'],
            'Dairy' => ['/seed/products/sua-1.jpg', '/seed/products/sua-2.jpg'],
            'Herbs' => ['/seed/products/thao-moc-1.jpg', '/seed/products/thao-moc-2.jpg'],
            'Organic' => ['/seed/products/huu-co-1.jpg', '/seed/products/huu-co-2.jpg'],
            'Legumes' => ['/seed/products/dau-hat-1.jpg', '/seed/products/dau-hat-2.jpg'],
            'Spices' => ['/seed/products/gia-vi-1.jpg', '/seed/products/gia-vi-2.jpg'],
            'Mushrooms' => ['/seed/products/nam-1.jpg', '/seed/products/nam-2.jpg'],
            'Honey' => ['/seed/products/mat-ong-1.jpg', '/seed/products/mat-ong-2.jpg'],
        ];

        $prices = [
            'Vegetables' => 12000,
            'Fruits' => 25000,
            'Grains' => 28000,
            'Dairy' => 18000,
            'Herbs' => 8000,
            'Organic' => 22000,
            'Legumes' => 30000,
            'Spices' => 15000,
            'Mushrooms' => 35000,
            'Honey' => 85000,
        ];

        $index = 0;

        foreach ($catalog as $categoryName => $names) {
            $category = $categories[$categoryName];

            foreach ($names as $offset => $name) {
                $farmer = $farmers[$index % $farmers->count()];

                $this->createAudited(Product::class, [
                    'farmer_id' => $farmer->id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'description' => $name.' harvested the same day at '.$farmer->business_name.'.',
                    'price' => $prices[$categoryName] + ($offset * 2000),
                    'stock_qty' => 80,
                    'image_url' => $images[$categoryName],
                ]);

                $index++;
            }
        }
    }
}
