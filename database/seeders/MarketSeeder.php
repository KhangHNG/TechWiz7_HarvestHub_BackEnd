<?php

namespace Database\Seeders;

use App\Models\Market;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $markets = [
            ['Chợ Nông Sản Quận 1', '123 Nguyễn Huệ, Quận 1, TP.HCM', 10.77560000, 106.70190000, '06:00 - 18:00'],
            ['Chợ Đầu Mối Thủ Đức', '456 Kha Vạn Cân, Thủ Đức, TP.HCM', 10.84940000, 106.75370000, '04:00 - 12:00'],
            ['Chợ Bến Thành', 'Lê Lợi, Quận 1, TP.HCM', 10.77250000, 106.69800000, '06:00 - 18:00'],
            ['Chợ Tân Định', 'Hải Thượng Lãn Ông, Quận 1, TP.HCM', 10.78890000, 106.69070000, '05:00 - 17:00'],
            ['Chợ Hòa Bình', 'Đường 3 Tháng 2, Quận 5, TP.HCM', 10.75520000, 106.66680000, '05:30 - 18:30'],
            ['Chợ Phạm Văn Hai', 'Phạm Văn Hai, Tân Bình, TP.HCM', 10.79410000, 106.65820000, '06:00 - 19:00'],
            ['Chợ Bà Chiểu', 'Bạch Đằng, Bình Thạnh, TP.HCM', 10.81280000, 106.69850000, '05:00 - 18:00'],
            ['Chợ Thái Bình', 'Phạm Ngũ Lão, Quận 1, TP.HCM', 10.76820000, 106.69240000, '06:00 - 17:00'],
            ['Chợ An Đông', 'An Dương Vương, Quận 5, TP.HCM', 10.75460000, 106.67210000, '06:00 - 18:00'],
            ['Chợ Gò Vấp', 'Quang Trung, Gò Vấp, TP.HCM', 10.83820000, 106.66540000, '04:30 - 12:00'],
        ];

        foreach ($markets as [$name, $address, $latitude, $longitude, $hours]) {
            Market::create([
                'name' => $name,
                'address' => $address,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'operating_hours' => $hours,
                'is_active' => true,
            ]);
        }
    }
}
