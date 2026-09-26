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
            ['Nông Trại Lê Văn Nông', 'Rau củ sạch trồng theo tiêu chuẩn VietGAP, thu hoạch mỗi sáng.', 4.5],
            ['Vườn Trái Cây Phạm Thị Vườn', 'Trái cây chín cây, hái trong ngày và giao tại chợ.', 4.8],
            ['Nông Trại Trần Văn Đất', 'Lúa và ngũ cốc sạch, đóng bao đúng cân.', 4.2],
            ['Vườn Rau Nguyễn Thị Lúa', 'Rau ăn lá theo mùa, không thuốc trừ sâu.', 4.6],
            ['Nông Trại Võ Minh Vườn', 'Nấm tươi và rau thủy canh, hái theo đơn.', 4.4],
            ['Vườn Đinh Công Cày', 'Đậu, hạt và nông sản khô phơi nắng.', 4.1],
            ['Nông Trại Huỳnh Thị Mạ', 'Thảo mộc và gia vị tươi cắt trong ngày.', 4.7],
            ['Vườn Phan Văn Rẫy', 'Trái cây nhiệt đới, bán sỉ và lẻ tại chợ.', 4.3],
            ['Nông Trại Lâm Thị Sen', 'Rau hữu cơ có ghi nhật ký chăm sóc.', 4.9],
            ['Vườn Tô Quốc Thắng', 'Mật ong nguyên chất và sản phẩm từ ong.', 4.6],
        ];

        foreach ($profiles as $index => [$businessName, $description, $rating]) {
            $this->createAudited(Farmer::class, [
                'user_id' => $users[$index]->id,
                'market_id' => $markets[$index % $markets->count()]->id,
                'business_name' => $businessName,
                'description' => $description,
                'rating' => $rating,
            ]);
        }
    }
}
