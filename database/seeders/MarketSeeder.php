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
            ['Ben Thanh Market', 'Le Loi Street, Ben Thanh Ward, District 1, Ho Chi Minh City, Vietnam', 10.77254000, 106.69801300, '06:00 - 18:00'],
            ['Binh Tay Market', '57A Thap Muoi Street, District 6, Ho Chi Minh City, Vietnam', 10.74940000, 106.65120000, '06:00 - 18:00'],
            ['Dong Xuan Market', 'Dong Xuan Street, Hoan Kiem District, Hanoi, Vietnam', 21.03820000, 105.84950000, '06:00 - 18:00'],
            ['Hom Market', '81 Hue Street, Hai Ba Trung District, Hanoi, Vietnam', 21.01680000, 105.85120000, '06:00 - 19:00'],
            ['Han Market', '119 Tran Phu Street, Hai Chau District, Da Nang, Vietnam', 16.06780000, 108.22340000, '06:00 - 18:00'],
            ['Dam Market', 'Tran Phu Street, Nha Trang, Khanh Hoa, Vietnam', 12.23880000, 109.19670000, '05:00 - 18:00'],
            ['Crawford Market', 'Dr Dadabhai Naoroji Road, Fort, Mumbai, Maharashtra 400001, India', 18.94770000, 72.83470000, '10:00 - 20:00'],
            ['Khari Baoli', 'Khari Baoli Road, Chandni Chowk, Delhi 110006, India', 28.65620000, 77.21670000, '09:00 - 20:00'],
            ['KR Market', 'Kalasipalya, Bengaluru, Karnataka 560002, India', 12.96560000, 77.57760000, '06:00 - 21:00'],
            ['Koyambedu Market', 'Koyambedu, Chennai, Tamil Nadu 600107, India', 13.06940000, 80.19480000, '04:00 - 14:00'],
            ['New Market', 'Lindsay Street, Kolkata, West Bengal 700087, India', 22.56000000, 88.35250000, '10:00 - 20:00'],
            ['Laad Bazaar', 'Charminar, Hyderabad, Telangana 500002, India', 17.36160000, 78.47470000, '11:00 - 22:00'],
        ];

        foreach ($markets as [$name, $address, $latitude, $longitude, $hours]) {
            $this->createAudited(Market::class, [
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
