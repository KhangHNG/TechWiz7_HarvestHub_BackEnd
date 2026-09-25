<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Rau củ',
            'Trái cây',
            'Ngũ cốc',
            'Sữa',
            'Thảo mộc',
            'Hữu cơ',
            'Đậu hạt',
            'Gia vị',
            'Nấm',
            'Mật ong',
        ];

        foreach ($names as $name) {
            Category::create(['name' => $name]);
        }
    }
}
