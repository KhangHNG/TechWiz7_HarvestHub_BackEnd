<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        User::create([
            'full_name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'phone' => '0900000000',
            'password_hash' => $password,
            'address' => '1 Lê Duẩn, Quận 1, TP.HCM',
            'role' => 'ADMIN',
        ]);

        $customers = [
            ['Nguyễn Văn An', '12 Lê Lợi, Quận 1, TP.HCM', 'Hồ Chí Minh', 'Quận 1', 'TP.HCM'],
            ['Trần Thị Bình', '34 Điện Biên Phủ, Bình Thạnh, TP.HCM', 'Hồ Chí Minh', 'Bình Thạnh', 'TP.HCM'],
            ['Lê Hoàng Cường', '56 Nguyễn Văn Cừ, Quận 5, TP.HCM', 'Hồ Chí Minh', 'Quận 5', 'TP.HCM'],
            ['Phạm Thu Dung', '78 Cách Mạng Tháng 8, Quận 3, TP.HCM', 'Hồ Chí Minh', 'Quận 3', 'TP.HCM'],
            ['Hoàng Minh Đức', '90 Phan Xích Long, Phú Nhuận, TP.HCM', 'Hồ Chí Minh', 'Phú Nhuận', 'TP.HCM'],
            ['Võ Thị Em', '15 Nguyễn Thị Minh Khai, Quận 1, TP.HCM', 'Hồ Chí Minh', 'Quận 1', 'TP.HCM'],
            ['Đặng Quốc Phong', '27 Lý Thường Kiệt, Quận 10, TP.HCM', 'Hồ Chí Minh', 'Quận 10', 'TP.HCM'],
            ['Bùi Ngọc Giang', '41 Võ Văn Tần, Quận 3, TP.HCM', 'Hồ Chí Minh', 'Quận 3', 'TP.HCM'],
            ['Ngô Thanh Hà', '63 Trần Hưng Đạo, Quận 5, TP.HCM', 'Hồ Chí Minh', 'Quận 5', 'TP.HCM'],
            ['Đỗ Kim Yến', '88 Hoàng Văn Thụ, Tân Bình, TP.HCM', 'Hồ Chí Minh', 'Tân Bình', 'TP.HCM'],
            ['Mai Quốc Khánh', '19 Pasteur, Quận 1, TP.HCM', 'Hồ Chí Minh', 'Quận 1', 'TP.HCM'],
            ['Lý Thu Trang', '102 Nguyễn Đình Chiểu, Quận 3, TP.HCM', 'Hồ Chí Minh', 'Quận 3', 'TP.HCM'],
        ];

        foreach ($customers as $index => [$name, $address, $city, $district, $capital]) {
            $n = $index + 1;
            User::create([
                'full_name' => $name,
                'email' => "customer{$n}@example.com",
                'phone' => '0901'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'password_hash' => $password,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'capital' => $capital,
                'role' => 'CUSTOMER',
            ]);
        }

        $farmers = [
            ['Lê Văn Nông', 'Ấp 2, Củ Chi, TP.HCM', 'Hồ Chí Minh', 'Củ Chi', 'TP.HCM'],
            ['Phạm Thị Vườn', 'Xã Tân Hiệp, Hóc Môn, TP.HCM', 'Hồ Chí Minh', 'Hóc Môn', 'TP.HCM'],
            ['Trần Văn Đất', 'Xã Phạm Văn Cội, Củ Chi, TP.HCM', 'Hồ Chí Minh', 'Củ Chi', 'TP.HCM'],
            ['Nguyễn Thị Lúa', 'Xã Bình Mỹ, Củ Chi, TP.HCM', 'Hồ Chí Minh', 'Củ Chi', 'TP.HCM'],
            ['Võ Minh Vườn', 'Xã Đông Thạnh, Hóc Môn, TP.HCM', 'Hồ Chí Minh', 'Hóc Môn', 'TP.HCM'],
            ['Đinh Công Cày', 'Xã Tân Phú Trung, Củ Chi, TP.HCM', 'Hồ Chí Minh', 'Củ Chi', 'TP.HCM'],
            ['Huỳnh Thị Mạ', 'Xã Trung Lập Thượng, Củ Chi, TP.HCM', 'Hồ Chí Minh', 'Củ Chi', 'TP.HCM'],
            ['Phan Văn Rẫy', 'Xã Xuân Thới Sơn, Hóc Môn, TP.HCM', 'Hồ Chí Minh', 'Hóc Môn', 'TP.HCM'],
            ['Lâm Thị Sen', 'Xã Phước Vĩnh An, Củ Chi, TP.HCM', 'Hồ Chí Minh', 'Củ Chi', 'TP.HCM'],
            ['Tô Quốc Thắng', 'Xã Tân Thới Nhì, Hóc Môn, TP.HCM', 'Hồ Chí Minh', 'Hóc Môn', 'TP.HCM'],
        ];

        foreach ($farmers as $index => [$name, $address, $city, $district, $capital]) {
            $n = $index + 1;
            User::create([
                'full_name' => $name,
                'email' => "farmer{$n}@example.com",
                'phone' => '0902'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'password_hash' => $password,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'capital' => $capital,
                'role' => 'FARMER',
            ]);
        }
    }
}
