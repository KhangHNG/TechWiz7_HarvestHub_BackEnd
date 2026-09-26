<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $farmers = Farmer::query()->orderBy('id')->get();
        $categories = Category::query()->orderBy('id')->get();

        $catalog = [
            'Rau muống', 'Cà chua bi', 'Xoài cát Hòa Lộc', 'Cải ngọt', 'Dưa hấu',
            'Gạo ST25', 'Sữa tươi', 'Húng quế', 'Rau hữu cơ mix', 'Đậu xanh',
            'Ớt hiểm', 'Nấm bào ngư', 'Mật ong hoa cà phê', 'Bắp cải', 'Chuối già',
            'Khoai lang', 'Bí đỏ', 'Dưa leo', 'Cà rốt', 'Hành lá',
            'Rau thơm', 'Xà lách', 'Cải thìa', 'Đậu cove', 'Mướp hương',
            'Khổ qua', 'Su hào', 'Súp lơ', 'Củ dền', 'Gừng',
            'Nghệ', 'Sả', 'Tỏi', 'Hành tím', 'Khoai môn',
            'Khoai tây', 'Bắp non', 'Đậu bắp', 'Rau ngót', 'Rau dền',
            'Mồng tơi', 'Cải xoong', 'Rau má', 'Dưa gang', 'Bí xanh',
            'Chanh', 'Tắc', 'Bưởi da xanh', 'Cam sành', 'Quýt đường',
            'Ổi nữ hoàng', 'Mận An Phước', 'Nhãn xuồng', 'Vải thiều', 'Măng cụt',
            'Sầu riêng', 'Thanh long', 'Dứa', 'Đu đủ', 'Mít thái',
            'Sapoche', 'Mãng cầu', 'Chôm chôm', 'Bơ sáp', 'Dừa xiêm',
            'Gạo tám thơm', 'Gạo nếp', 'Bột mì', 'Bột năng', 'Đậu đỏ',
            'Đậu đen', 'Đậu phộng', 'Hạt điều', 'Hạt sen', 'Mè đen',
            'Sữa chua', 'Phô mai tươi', 'Bơ lạt', 'Sữa hạt', 'Yaourt',
            'Tía tô', 'Lá lốt', 'Ngò gai', 'Rau răm', 'Diếp cá',
            'Nấm đùi gà', 'Nấm kim châm', 'Nấm rơm', 'Nấm linh chi', 'Đông trùng',
            'Mật ong rừng', 'Mật ong bạc hà', 'Phấn hoa', 'Sáp ong', 'Trà atiso',
            'Cà phê hạt', 'Tiêu đen', 'Quế', 'Hồi', 'Đinh hương',
        ];

        foreach ($catalog as $index => $name) {
            $farmer = $farmers[$index % $farmers->count()];
            $category = $categories[$index % $categories->count()];
            $price = 10000 + (($index % 20) * 5000);

            Product::create([
                'farmer_id' => $farmer->id,
                'category_id' => $category->id,
                'name' => $name,
                'description' => $name.' tươi, thu hoạch trong ngày từ '.$farmer->business_name.'.',
                'price' => $price,
                'stock_qty' => 20 + ($index % 80),
                'image_url' => [
                    'https://example.com/images/product-'.($index + 1).'-1.jpg',
                    'https://example.com/images/product-'.($index + 1).'-2.jpg',
                ],
            ]);
        }
    }
}
