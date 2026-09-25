<?php

namespace Database\Seeders;

use App\Models\Farmer;
use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Seeder;

class FarmerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('role', 'FARMER')->orderBy('id')->get();
        $markets = Market::query()->orderBy('id')->get();

        $profiles = [
            ['Nông Trại Lê Văn Nông', 'Rau củ sạch trồng theo tiêu chuẩn VietGAP.', 4.5],
            ['Vườn Trái Cây Phạm Thị Vườn', 'Trái cây tươi thu hoạch trong ngày.', 4.8],
            ['Nông Trại Trần Văn Đất', 'Chuyên lúa và ngũ cốc sạch.', 4.2],
            ['Vườn Rau Nguyễn Thị Lúa', 'Rau ăn lá theo mùa, không thuốc trừ sâu.', 4.6],
            ['Nông Trại Võ Minh Vườn', 'Cung cấp nấm và rau thủy canh.', 4.4],
            ['Vườn Đinh Công Cày', 'Đậu, hạt và nông sản khô.', 4.1],
            ['Nông Trại Huỳnh Thị Mạ', 'Thảo mộc và gia vị tươi.', 4.7],
            ['Vườn Phan Văn Rẫy', 'Trái cây nhiệt đới sỉ và lẻ.', 4.3],
            ['Nông Trại Lâm Thị Sen', 'Sản phẩm hữu cơ có chứng nhận.', 4.9],
            ['Vườn Tô Quốc Thắng', 'Mật ong và sản phẩm từ ong.', 4.0],
        ];

        foreach ($profiles as $index => [$businessName, $description, $rating]) {
            Farmer::create([
                'user_id' => $users[$index]->id,
                'market_id' => $markets[$index % $markets->count()]->id,
                'business_name' => $businessName,
                'description' => $description,
                'rating' => $rating,
            ]);
        }
    }
}
