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
            'address' => '1 Le Duan Street, Ben Nghe Ward, District 1, Ho Chi Minh City',
            'city' => 'Ho Chi Minh City',
            'district' => 'District 1',
            'ward' => 'Ben Nghe Ward',
            'capital' => 'Ho Chi Minh City',
            'avatar_url' => null,
            'role' => 'ADMIN',
            'email_verified_at' => $verifiedAt,
        ]);
        $admin->forceFill([
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ])->save();

        $customers = [
            ['Liam Nguyen', '12 Le Loi Street, Ben Thanh Ward, District 1, Ho Chi Minh City', 'Ho Chi Minh City', 'District 1', 'Ho Chi Minh City', 'Ben Thanh Ward'],
            ['Emma Tran', '34 Thap Muoi Street, Ward 1, District 6, Ho Chi Minh City', 'Ho Chi Minh City', 'District 6', 'Ho Chi Minh City', 'Ward 1'],
            ['Noah Pham', '18 Hang Dao Street, Hang Dao Ward, Hoan Kiem District, Hanoi', 'Hanoi', 'Hoan Kiem District', 'Hanoi', 'Hang Dao Ward'],
            ['Olivia Le', '81 Hue Street, Nguyen Du Ward, Hai Ba Trung District, Hanoi', 'Hanoi', 'Hai Ba Trung District', 'Hanoi', 'Nguyen Du Ward'],
            ['Ethan Vo', '119 Tran Phu Street, Hai Chau 1 Ward, Hai Chau District, Da Nang', 'Da Nang', 'Hai Chau District', 'Da Nang', 'Hai Chau 1 Ward'],
            ['Sophia Dinh', '10 Vo Nguyen Giap Street, Man Thai Ward, Son Tra District, Da Nang', 'Da Nang', 'Son Tra District', 'Da Nang', 'Man Thai Ward'],
            ['Arjun Mehta', '12 DN Road, Fort Ward, South Mumbai, Mumbai', 'Mumbai', 'South Mumbai', 'Mumbai', 'Fort Ward'],
            ['Priya Sharma', '45 Chandni Chowk, Chandni Chowk Ward, Central Delhi, New Delhi', 'New Delhi', 'Central Delhi', 'New Delhi', 'Chandni Chowk Ward'],
            ['Rahul Nair', '8 Kalasipalya Main Road, Kalasipalya Ward, Bengaluru Central, Bengaluru', 'Bengaluru', 'Bengaluru Central', 'Bengaluru', 'Kalasipalya Ward'],
            ['Ananya Iyer', '22 Koyambedu Market Road, Koyambedu Ward, Chennai Central, Chennai', 'Chennai', 'Chennai Central', 'Chennai', 'Koyambedu Ward'],
            ['Dev Patel', '15 Lindsay Street, Lindsay Street Ward, Kolkata Central, Kolkata', 'Kolkata', 'Kolkata Central', 'Kolkata', 'Lindsay Street Ward'],
            ['Fatima Khan', '7 Charminar Road, Charminar Ward, Hyderabad Central, Hyderabad', 'Hyderabad', 'Hyderabad Central', 'Hyderabad', 'Charminar Ward'],
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
                'avatar_url' => null,
                'role' => 'CUSTOMER',
                'email_verified_at' => $verifiedAt,
            ]);
        }

        $farmers = [
            ['Minh Le', '25 Le Loi Street, Ben Thanh Ward, District 1, Ho Chi Minh City', 'Ho Chi Minh City', 'District 1', 'Ho Chi Minh City', 'Ben Thanh Ward'],
            ['Hoa Pham', '57 Thap Muoi Street, Ward 1, District 6, Ho Chi Minh City', 'Ho Chi Minh City', 'District 6', 'Ho Chi Minh City', 'Ward 1'],
            ['Anh Tran', '4 Hang Dao Street, Hang Dao Ward, Hoan Kiem District, Hanoi', 'Hanoi', 'Hoan Kiem District', 'Hanoi', 'Hang Dao Ward'],
            ['Lan Nguyen', '90 Hue Street, Nguyen Du Ward, Hai Ba Trung District, Hanoi', 'Hanoi', 'Hai Ba Trung District', 'Hanoi', 'Nguyen Du Ward'],
            ['Khoa Vo', '130 Tran Phu Street, Hai Chau 1 Ward, Hai Chau District, Da Nang', 'Da Nang', 'Hai Chau District', 'Da Nang', 'Hai Chau 1 Ward'],
            ['Mai Dinh', '16 Vo Nguyen Giap Street, Man Thai Ward, Son Tra District, Da Nang', 'Da Nang', 'Son Tra District', 'Da Nang', 'Man Thai Ward'],
            ['Rohan Desai', '30 DN Road, Fort Ward, South Mumbai, Mumbai', 'Mumbai', 'South Mumbai', 'Mumbai', 'Fort Ward'],
            ['Neha Gupta', '12 Khari Baoli, Chandni Chowk Ward, Central Delhi, New Delhi', 'New Delhi', 'Central Delhi', 'New Delhi', 'Chandni Chowk Ward'],
            ['Vikram Rao', '19 Kalasipalya Main Road, Kalasipalya Ward, Bengaluru Central, Bengaluru', 'Bengaluru', 'Bengaluru Central', 'Bengaluru', 'Kalasipalya Ward'],
            ['Meera Krishnan', '40 Koyambedu Market Road, Koyambedu Ward, Chennai Central, Chennai', 'Chennai', 'Chennai Central', 'Chennai', 'Koyambedu Ward'],
            ['Amit Banerjee', '2 Lindsay Street, Lindsay Street Ward, Kolkata Central, Kolkata', 'Kolkata', 'Kolkata Central', 'Kolkata', 'Lindsay Street Ward'],
            ['Aisha Qureshi', '8 Laad Bazaar, Laad Bazaar Ward, Hyderabad Central, Hyderabad', 'Hyderabad', 'Hyderabad Central', 'Hyderabad', 'Laad Bazaar Ward'],
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
                'avatar_url' => null,
                'role' => 'FARMER',
                'email_verified_at' => $verifiedAt,
            ]);
        }
    }
}
