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
            'Rau củ' => ['Rau muống', 'Cải ngọt', 'Cà chua bi', 'Dưa leo', 'Cà rốt', 'Hành lá', 'Xà lách', 'Cải thìa', 'Khoai lang', 'Bí đỏ'],
            'Trái cây' => ['Xoài cát Hòa Lộc', 'Dưa hấu', 'Chuối già', 'Bưởi da xanh', 'Cam sành', 'Ổi nữ hoàng', 'Nhãn xuồng', 'Thanh long', 'Đu đủ', 'Mít thái'],
            'Ngũ cốc' => ['Gạo ST25', 'Gạo tám thơm', 'Gạo nếp', 'Bột mì', 'Bột năng', 'Bắp non', 'Khoai tây', 'Khoai môn', 'Yến mạch', 'Hạt bo bo'],
            'Sữa' => ['Sữa tươi', 'Sữa chua', 'Phô mai tươi', 'Bơ lạt', 'Sữa hạt', 'Yaourt', 'Sữa đặc', 'Sữa chua uống', 'Phô mai que', 'Kem tươi'],
            'Thảo mộc' => ['Húng quế', 'Rau thơm', 'Tía tô', 'Lá lốt', 'Ngò gai', 'Rau răm', 'Diếp cá', 'Rau má', 'Sả', 'Gừng'],
            'Hữu cơ' => ['Rau hữu cơ mix', 'Cải xoong', 'Mồng tơi', 'Rau ngót', 'Rau dền', 'Su hào', 'Súp lơ', 'Củ dền', 'Bí xanh', 'Mướp hương'],
            'Đậu hạt' => ['Đậu xanh', 'Đậu cove', 'Đậu đỏ', 'Đậu đen', 'Đậu phộng', 'Hạt điều', 'Hạt sen', 'Mè đen', 'Đậu bắp', 'Đậu Hà Lan'],
            'Gia vị' => ['Ớt hiểm', 'Nghệ', 'Tỏi', 'Hành tím', 'Tiêu đen', 'Quế', 'Hồi', 'Đinh hương', 'Chanh', 'Tắc'],
            'Nấm' => ['Nấm bào ngư', 'Nấm đùi gà', 'Nấm kim châm', 'Nấm rơm', 'Nấm linh chi', 'Đông trùng hạ thảo', 'Nấm hương', 'Nấm mỡ', 'Nấm tuyết', 'Nấm hoàng đế'],
            'Mật ong' => ['Mật ong hoa cà phê', 'Mật ong rừng', 'Mật ong bạc hà', 'Phấn hoa', 'Sáp ong', 'Trà atiso', 'Cà phê hạt', 'Sữa ong chúa', 'Mật ong hoa nhãn', 'Mật ong hoa vải'],
        ];

        $images = [
            'Rau củ' => ['/seed/products/rau-cu-1.jpg', '/seed/products/rau-cu-2.jpg'],
            'Trái cây' => ['/seed/products/trai-cay-1.jpg', '/seed/products/trai-cay-2.jpg'],
            'Ngũ cốc' => ['/seed/products/ngu-coc-1.jpg', '/seed/products/ngu-coc-2.jpg'],
            'Sữa' => ['/seed/products/sua-1.jpg', '/seed/products/sua-2.jpg'],
            'Thảo mộc' => ['/seed/products/thao-moc-1.jpg', '/seed/products/thao-moc-2.jpg'],
            'Hữu cơ' => ['/seed/products/huu-co-1.jpg', '/seed/products/huu-co-2.jpg'],
            'Đậu hạt' => ['/seed/products/dau-hat-1.jpg', '/seed/products/dau-hat-2.jpg'],
            'Gia vị' => ['/seed/products/gia-vi-1.jpg', '/seed/products/gia-vi-2.jpg'],
            'Nấm' => ['/seed/products/nam-1.jpg', '/seed/products/nam-2.jpg'],
            'Mật ong' => ['/seed/products/mat-ong-1.jpg', '/seed/products/mat-ong-2.jpg'],
        ];

        $prices = [
            'Rau củ' => 12000,
            'Trái cây' => 25000,
            'Ngũ cốc' => 28000,
            'Sữa' => 18000,
            'Thảo mộc' => 8000,
            'Hữu cơ' => 22000,
            'Đậu hạt' => 30000,
            'Gia vị' => 15000,
            'Nấm' => 35000,
            'Mật ong' => 85000,
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
                    'description' => $name.' tươi, thu hoạch trong ngày từ '.$farmer->business_name.'.',
                    'price' => $prices[$categoryName] + ($offset * 2000),
                    'stock_qty' => 80,
                    'image_url' => $images[$categoryName],
                ]);

                $index++;
            }
        }
    }
}
