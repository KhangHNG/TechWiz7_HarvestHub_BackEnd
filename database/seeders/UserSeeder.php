<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $password = Hash::make('password123');
        $verifiedAt = now();

        $admin = User::create([
            'full_name' => 'Alex Morgan',
            'email' => 'admin@example.com',
            'phone' => '0900000001',
            'password_hash' => $password,
            'address' => '1 Le Duan Street, District 1, Ho Chi Minh City',
            'city' => 'Ho Chi Minh City',
            'district' => 'District 1',
            'capital' => 'Ho Chi Minh City',
            'avatar_url' => '/seed/products/rau-cu-1.jpg',
            'role' => 'ADMIN',
            'email_verified_at' => $verifiedAt,
        ]);
        $admin->forceFill([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ])->save();

        $customers = [
            ['Liam Nguyen', '12 Le Loi Street, Ben Thanh Ward, District 1, Ho Chi Minh City', 'Ho Chi Minh City', 'District 1', 'Ho Chi Minh City'],
            ['Emma Tran', '34 Thap Muoi Street, District 6, Ho Chi Minh City', 'Ho Chi Minh City', 'District 6', 'Ho Chi Minh City'],
            ['Noah Pham', '18 Dong Xuan Street, Hoan Kiem District, Hanoi', 'Hanoi', 'Hoan Kiem', 'Hanoi'],
            ['Olivia Le', '81 Hue Street, Hai Ba Trung District, Hanoi', 'Hanoi', 'Hai Ba Trung', 'Hanoi'],
            ['Ethan Vo', '119 Tran Phu Street, Hai Chau District, Da Nang', 'Da Nang', 'Hai Chau', 'Da Nang'],
            ['Sophia Dinh', '10 Tran Phu Street, Nha Trang, Khanh Hoa', 'Nha Trang', 'Nha Trang', 'Khanh Hoa'],
            ['Arjun Mehta', '12 Dadabhai Naoroji Road, Fort, Mumbai, Maharashtra 400001', 'Mumbai', 'Fort', 'Maharashtra'],
            ['Priya Sharma', '45 Khari Baoli Road, Chandni Chowk, Delhi 110006', 'Delhi', 'Chandni Chowk', 'Delhi'],
            ['Rahul Nair', '8 Kalasipalya Main Road, Bengaluru, Karnataka 560002', 'Bengaluru', 'Kalasipalya', 'Karnataka'],
            ['Ananya Iyer', '22 Koyambedu Market Road, Chennai, Tamil Nadu 600107', 'Chennai', 'Koyambedu', 'Tamil Nadu'],
            ['Dev Patel', '15 Lindsay Street, Kolkata, West Bengal 700087', 'Kolkata', 'Lindsay Street', 'West Bengal'],
            ['Fatima Khan', '7 Charminar Road, Hyderabad, Telangana 500002', 'Hyderabad', 'Charminar', 'Telangana'],
        ];

        foreach ($customers as $index => [$name, $address, $city, $district, $capital]) {
            $n = $index + 1;
            $this->createAudited(User::class, [
                'full_name' => $name,
                'email' => "customer{$n}@example.com",
                'phone' => '0901'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'password_hash' => $password,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'capital' => $capital,
                'avatar_url' => '/seed/products/trai-cay-'.(($index % 2) + 1).'.jpg',
                'role' => 'CUSTOMER',
                'email_verified_at' => $verifiedAt,
            ]);
        }

        $farmers = [
            ['Minh Le', '25 Le Loi Street, District 1, Ho Chi Minh City', 'Ho Chi Minh City', 'District 1', 'Ho Chi Minh City'],
            ['Hoa Pham', '57 Thap Muoi Street, District 6, Ho Chi Minh City', 'Ho Chi Minh City', 'District 6', 'Ho Chi Minh City'],
            ['Anh Tran', '4 Dong Xuan Street, Hoan Kiem District, Hanoi', 'Hanoi', 'Hoan Kiem', 'Hanoi'],
            ['Lan Nguyen', '90 Hue Street, Hai Ba Trung District, Hanoi', 'Hanoi', 'Hai Ba Trung', 'Hanoi'],
            ['Khoa Vo', '130 Tran Phu Street, Hai Chau District, Da Nang', 'Da Nang', 'Hai Chau', 'Da Nang'],
            ['Mai Dinh', '16 Tran Phu Street, Nha Trang, Khanh Hoa', 'Nha Trang', 'Nha Trang', 'Khanh Hoa'],
            ['Rohan Desai', '30 Dadabhai Naoroji Road, Fort, Mumbai, Maharashtra 400001', 'Mumbai', 'Fort', 'Maharashtra'],
            ['Neha Gupta', '12 Khari Baoli Road, Chandni Chowk, Delhi 110006', 'Delhi', 'Chandni Chowk', 'Delhi'],
            ['Vikram Rao', '19 Kalasipalya Main Road, Bengaluru, Karnataka 560002', 'Bengaluru', 'Kalasipalya', 'Karnataka'],
            ['Meera Krishnan', '40 Koyambedu Market Road, Chennai, Tamil Nadu 600107', 'Chennai', 'Koyambedu', 'Tamil Nadu'],
            ['Amit Banerjee', '6 Lindsay Street, Kolkata, West Bengal 700087', 'Kolkata', 'Lindsay Street', 'West Bengal'],
            ['Aisha Qureshi', '3 Charminar Road, Hyderabad, Telangana 500002', 'Hyderabad', 'Charminar', 'Telangana'],
        ];

        foreach ($farmers as $index => [$name, $address, $city, $district, $capital]) {
            $n = $index + 1;
            $this->createAudited(User::class, [
                'full_name' => $name,
                'email' => "farmer{$n}@example.com",
                'phone' => '0902'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'password_hash' => $password,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'capital' => $capital,
                'avatar_url' => '/seed/products/huu-co-'.(($index % 2) + 1).'.jpg',
                'role' => 'FARMER',
                'email_verified_at' => $verifiedAt,
            ]);
        }
    }
}
