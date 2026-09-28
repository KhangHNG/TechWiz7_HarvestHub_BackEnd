<?php

namespace Database\Seeders;

use App\Models\Market;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $markets = [
            ['Ben Thanh Market', 'Lê Lợi, Phường Bến Thành, Quận 1, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận 1', 'Phường Bến Thành', 10.77254000, 106.69801300, '06:00 - 18:00'],
            ['Binh Tay Market', 'Tháp Mười, Phường 1, Quận 6, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận 6', 'Phường 1', 10.74940000, 106.65120000, '06:00 - 18:00'],
            ['Dong Xuan Market', 'Hàng Đào, Phường Hàng Đào, Quận Hoàn Kiếm, Hà Nội', 'Hà Nội', 'Quận Hoàn Kiếm', 'Phường Hàng Đào', 21.03820000, 105.84950000, '06:00 - 18:00'],
            ['Hom Market', 'Huế, Phường Nguyễn Du, Quận Hai Bà Trưng, Hà Nội', 'Hà Nội', 'Quận Hai Bà Trưng', 'Phường Nguyễn Du', 21.01680000, 105.85120000, '06:00 - 19:00'],
            ['Han Market', 'Trần Phú, Phường Hải Châu 1, Quận Hải Châu, Đà Nẵng', 'Đà Nẵng', 'Quận Hải Châu', 'Phường Hải Châu 1', 16.06780000, 108.22340000, '06:00 - 18:00'],
            ['Dam Market', 'Võ Nguyên Giáp, Phường Mân Thái, Quận Sơn Trà, Đà Nẵng', 'Đà Nẵng', 'Quận Sơn Trà', 'Phường Mân Thái', 16.07820000, 108.24510000, '05:00 - 18:00'],
            ['Crawford Market', 'Lạch Tray, Phường Lê Lợi, Quận Ngô Quyền, Hải Phòng', 'Hải Phòng', 'Quận Ngô Quyền', 'Phường Lê Lợi', 20.86080000, 106.68810000, '10:00 - 20:00'],
            ['Khari Baoli', 'Trần Nguyên Hãn, Phường An Biên, Quận Lê Chân, Hải Phòng', 'Hải Phòng', 'Quận Lê Chân', 'Phường An Biên', 20.84820000, 106.68040000, '09:00 - 20:00'],
            ['KR Market', 'Hòa Bình, Phường Cái Khế, Quận Ninh Kiều, Cần Thơ', 'Cần Thơ', 'Quận Ninh Kiều', 'Phường Cái Khế', 10.04520000, 105.74690000, '06:00 - 21:00'],
            ['Koyambedu Market', 'Hưng Phú, Phường Hưng Phú, Quận Cái Răng, Cần Thơ', 'Cần Thơ', 'Quận Cái Răng', 'Phường Hưng Phú', 10.00180000, 105.76040000, '04:00 - 14:00'],
            ['New Market', 'Quốc lộ 22, Thị trấn Củ Chi, Huyện Củ Chi, Hồ Chí Minh', 'Hồ Chí Minh', 'Huyện Củ Chi', 'Thị trấn Củ Chi', 10.97340000, 106.49330000, '04:00 - 12:00'],
            ['Laad Bazaar', 'Nguyên Khê, Xã Nguyên Khê, Huyện Đông Anh, Hà Nội', 'Hà Nội', 'Huyện Đông Anh', 'Xã Nguyên Khê', 21.14020000, 105.84980000, '05:00 - 13:00'],
        ];

        foreach ($markets as [$name, $address, $city, $district, $ward, $latitude, $longitude, $hours]) {
            $this->createAudited(Market::class, [
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'ward' => $ward,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'operating_hours' => $hours,
                'is_active' => true,
            ]);
        }
    }
}
