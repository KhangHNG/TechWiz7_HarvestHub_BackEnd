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
            ['Ben Thanh Market', 'Le Loi Street, Ben Thanh Ward, District 1, Ho Chi Minh City', 'Ho Chi Minh City', 'District 1', 'Ben Thanh Ward', 10.77254000, 106.69801300, '06:00 - 18:00'],
            ['Binh Tay Market', 'Thap Muoi Street, Ward 1, District 6, Ho Chi Minh City', 'Ho Chi Minh City', 'District 6', 'Ward 1', 10.74940000, 106.65120000, '06:00 - 18:00'],
            ['Dong Xuan Market', 'Hang Dao Street, Hang Dao Ward, Hoan Kiem District, Hanoi', 'Hanoi', 'Hoan Kiem District', 'Hang Dao Ward', 21.03820000, 105.84950000, '06:00 - 18:00'],
            ['Hom Market', 'Hue Street, Nguyen Du Ward, Hai Ba Trung District, Hanoi', 'Hanoi', 'Hai Ba Trung District', 'Nguyen Du Ward', 21.01680000, 105.85120000, '06:00 - 19:00'],
            ['Han Market', 'Tran Phu Street, Hai Chau 1 Ward, Hai Chau District, Da Nang', 'Da Nang', 'Hai Chau District', 'Hai Chau 1 Ward', 16.06780000, 108.22340000, '06:00 - 18:00'],
            ['Dam Market', 'Vo Nguyen Giap Street, Man Thai Ward, Son Tra District, Da Nang', 'Da Nang', 'Son Tra District', 'Man Thai Ward', 16.07820000, 108.24510000, '05:00 - 18:00'],
            ['Crawford Market', 'DN Road, Fort Ward, South Mumbai, Mumbai', 'Mumbai', 'South Mumbai', 'Fort Ward', 18.94740000, 72.83470000, '10:00 - 20:00'],
            ['Khari Baoli', 'Khari Baoli, Chandni Chowk Ward, Central Delhi, New Delhi', 'New Delhi', 'Central Delhi', 'Chandni Chowk Ward', 28.65620000, 77.22100000, '09:00 - 20:00'],
            ['KR Market', 'Kalasipalya Main Road, Kalasipalya Ward, Bengaluru Central, Bengaluru', 'Bengaluru', 'Bengaluru Central', 'Kalasipalya Ward', 12.96160000, 77.57730000, '06:00 - 21:00'],
            ['Koyambedu Market', 'Koyambedu Market Road, Koyambedu Ward, Chennai Central, Chennai', 'Chennai', 'Chennai Central', 'Koyambedu Ward', 13.06980000, 80.19480000, '04:00 - 14:00'],
            ['New Market', 'Lindsay Street, Lindsay Street Ward, Kolkata Central, Kolkata', 'Kolkata', 'Kolkata Central', 'Lindsay Street Ward', 22.56010000, 88.35270000, '10:00 - 20:00'],
            ['Laad Bazaar', 'Laad Bazaar, Laad Bazaar Ward, Hyderabad Central, Hyderabad', 'Hyderabad', 'Hyderabad Central', 'Laad Bazaar Ward', 17.36160000, 78.47470000, '10:00 - 21:00'],
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
