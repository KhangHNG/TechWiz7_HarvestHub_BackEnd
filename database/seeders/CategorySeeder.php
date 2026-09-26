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
        ] as $name) {
            $this->createAudited(Category::class, ['name' => $name]);
        }
    }
}
