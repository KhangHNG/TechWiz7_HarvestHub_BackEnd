<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        // Pictures sit in public/seed/categories; the API turns each path
        // into a full link.
        foreach ([
            'Vegetables' => 'vegetables.webp',
            'Fruits' => 'fruits.webp',
            'Grains' => 'grains.webp',
            'Dairy' => 'dairy.webp',
            'Herbs' => 'herbs.webp',
            'Organic' => 'organic.webp',
            'Legumes' => 'legumes.webp',
            'Spices' => 'spices.webp',
            'Mushrooms' => 'mushrooms.webp',
            'Honey' => 'honey.webp',
        ] as $name => $picture) {
            $this->createAudited(Category::class, [
                'name' => $name,
                'image_url' => 'seed/categories/'.$picture,
            ]);
        }
    }
}
