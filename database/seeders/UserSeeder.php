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
            'address' => '1 Lê Duẩn, Phường Bến Nghé, Quận 1, Hồ Chí Minh',
            'city' => 'Hồ Chí Minh',
            'district' => 'Quận 1',
            'ward' => 'Phường Bến Nghé',
            'capital' => 'Hồ Chí Minh',
            'avatar_url' => '/seed/products/rau-cu-1.jpg',
            'role' => 'ADMIN',
            'email_verified_at' => $verifiedAt,
        ]);
        $admin->forceFill([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ])->save();

        $customers = [
            ['Liam Nguyen', '12 Lê Lợi, Phường Bến Thành, Quận 1, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận 1', 'Hồ Chí Minh', 'Phường Bến Thành'],
            ['Emma Tran', '34 Tháp Mười, Phường 1, Quận 6, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận 6', 'Hồ Chí Minh', 'Phường 1'],
            ['Noah Pham', '18 Hàng Đào, Phường Hàng Đào, Quận Hoàn Kiếm, Hà Nội', 'Hà Nội', 'Quận Hoàn Kiếm', 'Hà Nội', 'Phường Hàng Đào'],
            ['Olivia Le', '81 Huế, Phường Nguyễn Du, Quận Hai Bà Trưng, Hà Nội', 'Hà Nội', 'Quận Hai Bà Trưng', 'Hà Nội', 'Phường Nguyễn Du'],
            ['Ethan Vo', '119 Trần Phú, Phường Hải Châu 1, Quận Hải Châu, Đà Nẵng', 'Đà Nẵng', 'Quận Hải Châu', 'Đà Nẵng', 'Phường Hải Châu 1'],
            ['Sophia Dinh', '10 Võ Nguyên Giáp, Phường Mân Thái, Quận Sơn Trà, Đà Nẵng', 'Đà Nẵng', 'Quận Sơn Trà', 'Đà Nẵng', 'Phường Mân Thái'],
            ['Arjun Mehta', '12 Lạch Tray, Phường Lê Lợi, Quận Ngô Quyền, Hải Phòng', 'Hải Phòng', 'Quận Ngô Quyền', 'Hải Phòng', 'Phường Lê Lợi'],
            ['Priya Sharma', '45 Trần Nguyên Hãn, Phường An Biên, Quận Lê Chân, Hải Phòng', 'Hải Phòng', 'Quận Lê Chân', 'Hải Phòng', 'Phường An Biên'],
            ['Rahul Nair', '8 Hòa Bình, Phường Cái Khế, Quận Ninh Kiều, Cần Thơ', 'Cần Thơ', 'Quận Ninh Kiều', 'Cần Thơ', 'Phường Cái Khế'],
            ['Ananya Iyer', '22 Hưng Phú, Phường Hưng Phú, Quận Cái Răng, Cần Thơ', 'Cần Thơ', 'Quận Cái Răng', 'Cần Thơ', 'Phường Hưng Phú'],
            ['Dev Patel', '15 Điện Biên Phủ, Phường 1, Quận Bình Thạnh, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận Bình Thạnh', 'Hồ Chí Minh', 'Phường 1'],
            ['Fatima Khan', '7 Đội Cấn, Phường Điện Biên, Quận Ba Đình, Hà Nội', 'Hà Nội', 'Quận Ba Đình', 'Hà Nội', 'Phường Điện Biên'],
        ];

        foreach ($customers as $index => [$name, $address, $city, $district, $capital, $ward]) {
            $n = $index + 1;
            $this->createAudited(User::class, [
                'full_name' => $name,
                'email' => "customer{$n}@example.com",
                'phone' => '0901'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'password_hash' => $password,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'ward' => $ward,
                'capital' => $capital,
                'avatar_url' => '/seed/products/trai-cay-'.(($index % 2) + 1).'.jpg',
                'role' => 'CUSTOMER',
                'email_verified_at' => $verifiedAt,
            ]);
        }

        $farmers = [
            ['Minh Le', '25 Lê Lợi, Phường Bến Thành, Quận 1, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận 1', 'Hồ Chí Minh', 'Phường Bến Thành'],
            ['Hoa Pham', '57 Tháp Mười, Phường 1, Quận 6, Hồ Chí Minh', 'Hồ Chí Minh', 'Quận 6', 'Hồ Chí Minh', 'Phường 1'],
            ['Anh Tran', '4 Hàng Đào, Phường Hàng Đào, Quận Hoàn Kiếm, Hà Nội', 'Hà Nội', 'Quận Hoàn Kiếm', 'Hà Nội', 'Phường Hàng Đào'],
            ['Lan Nguyen', '90 Huế, Phường Nguyễn Du, Quận Hai Bà Trưng, Hà Nội', 'Hà Nội', 'Quận Hai Bà Trưng', 'Hà Nội', 'Phường Nguyễn Du'],
            ['Khoa Vo', '130 Trần Phú, Phường Hải Châu 1, Quận Hải Châu, Đà Nẵng', 'Đà Nẵng', 'Quận Hải Châu', 'Đà Nẵng', 'Phường Hải Châu 1'],
            ['Mai Dinh', '16 Võ Nguyên Giáp, Phường Mân Thái, Quận Sơn Trà, Đà Nẵng', 'Đà Nẵng', 'Quận Sơn Trà', 'Đà Nẵng', 'Phường Mân Thái'],
            ['Rohan Desai', '30 Lạch Tray, Phường Lê Lợi, Quận Ngô Quyền, Hải Phòng', 'Hải Phòng', 'Quận Ngô Quyền', 'Hải Phòng', 'Phường Lê Lợi'],
            ['Neha Gupta', '12 Trần Nguyên Hãn, Phường An Biên, Quận Lê Chân, Hải Phòng', 'Hải Phòng', 'Quận Lê Chân', 'Hải Phòng', 'Phường An Biên'],
            ['Vikram Rao', '19 Hòa Bình, Phường Cái Khế, Quận Ninh Kiều, Cần Thơ', 'Cần Thơ', 'Quận Ninh Kiều', 'Cần Thơ', 'Phường Cái Khế'],
            ['Meera Krishnan', '40 Hưng Phú, Phường Hưng Phú, Quận Cái Răng, Cần Thơ', 'Cần Thơ', 'Quận Cái Răng', 'Cần Thơ', 'Phường Hưng Phú'],
            ['Amit Banerjee', 'Ấp 2, Thị trấn Củ Chi, Huyện Củ Chi, Hồ Chí Minh', 'Hồ Chí Minh', 'Huyện Củ Chi', 'Hồ Chí Minh', 'Thị trấn Củ Chi'],
            ['Aisha Qureshi', 'Thôn Nguyên Khê, Xã Nguyên Khê, Huyện Đông Anh, Hà Nội', 'Hà Nội', 'Huyện Đông Anh', 'Hà Nội', 'Xã Nguyên Khê'],
        ];

        foreach ($farmers as $index => [$name, $address, $city, $district, $capital, $ward]) {
            $n = $index + 1;
            $this->createAudited(User::class, [
                'full_name' => $name,
                'email' => "farmer{$n}@example.com",
                'phone' => '0902'.str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'password_hash' => $password,
                'address' => $address,
                'city' => $city,
                'district' => $district,
                'ward' => $ward,
                'capital' => $capital,
                'avatar_url' => '/seed/products/huu-co-'.(($index % 2) + 1).'.jpg',
                'role' => 'FARMER',
                'email_verified_at' => $verifiedAt,
            ]);
        }
    }
}
