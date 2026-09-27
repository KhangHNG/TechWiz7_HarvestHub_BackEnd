<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        foreach ([
            'Vegetables',
            'Fruits',
            'Grains',
            'Dairy',
            'Herbs',
            'Organic',
            'Legumes',
            'Spices',
            'Mushrooms',
            'Honey',
        ] as $name) {
            $this->createAudited(Category::class, ['name' => $name]);
        }
    }
}
