<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\FarmerFollow;
use App\Models\Market;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 1. Users (Admin, Customers, Farmers) =====
        $admin = User::create([
            'full_name' => 'Quản trị viên',
            'email' => 'admin@example.com',
            'phone' => '0900000000',
            'password_hash' => Hash::make('password123'),
            'address' => 'Hà Nội',
            'role' => 'ADMIN',
        ]);

        $customer1 = User::create([
            'full_name' => 'Nguyễn Văn A',
            'email' => 'customer1@example.com',
            'phone' => '0901000001',
            'password_hash' => Hash::make('password123'),
            'address' => '12 Lê Lợi, Quận 1, TP.HCM',
            'role' => 'CUSTOMER',
        ]);

        $customer2 = User::create([
            'full_name' => 'Trần Thị B',
            'email' => 'customer2@example.com',
            'phone' => '0901000002',
            'password_hash' => Hash::make('password123'),
            'address' => '34 Điện Biên Phủ, Bình Thạnh, TP.HCM',
            'role' => 'CUSTOMER',
        ]);

        $farmerUser1 = User::create([
            'full_name' => 'Lê Văn Nông',
            'email' => 'farmer1@example.com',
            'phone' => '0901000003',
            'password_hash' => Hash::make('password123'),
            'address' => 'Củ Chi, TP.HCM',
            'role' => 'FARMER',
        ]);

        $farmerUser2 = User::create([
            'full_name' => 'Phạm Thị Vườn',
            'email' => 'farmer2@example.com',
            'phone' => '0901000004',
            'password_hash' => Hash::make('password123'),
            'address' => 'Hóc Môn, TP.HCM',
            'role' => 'FARMER',
        ]);

        // ===== 2. Markets =====
        $market1 = Market::create([
            'name' => 'Chợ Nông Sản Quận 1',
            'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'latitude' => 10.77560000,
            'longitude' => 106.70190000,
            'operating_hours' => '6:00 - 18:00',
            'is_active' => true,
        ]);

        $market2 = Market::create([
            'name' => 'Chợ Đầu Mối Thủ Đức',
            'address' => '456 Kha Vạn Cân, Thủ Đức, TP.HCM',
            'latitude' => 10.84940000,
            'longitude' => 106.75370000,
            'operating_hours' => '4:00 - 12:00',
            'is_active' => true,
        ]);

        // ===== 3. Farmers =====
        $farmer1 = Farmer::create([
            'user_id' => $farmerUser1->id,
            'market_id' => $market1->id,
            'business_name' => 'Nông Trại Lê Văn Nông',
            'description' => 'Chuyên cung cấp rau củ sạch trồng theo tiêu chuẩn VietGAP.',
            'rating' => 4.5,
        ]);

        $farmer2 = Farmer::create([
            'user_id' => $farmerUser2->id,
            'market_id' => $market2->id,
            'business_name' => 'Vườn Trái Cây Phạm Thị Vườn',
            'description' => 'Trái cây tươi thu hoạch trong ngày.',
            'rating' => 4.8,
        ]);

        // ===== 4. Categories =====
        $categoryNames = ['Fruits', 'Vegetables', 'Grains', 'Dairy', 'Herbs', 'Organic Products', 'Pulses'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = Category::create(['name' => $name]);
        }

        // ===== 5. Products =====
        $rauMuong = Product::create([
            'farmer_id' => $farmer1->id,
            'category_id' => $categories['Vegetables']->id,
            'name' => 'Rau muống',
            'description' => 'Rau muống sạch, trồng thủy canh.',
            'price' => 15000.0,
            'stock_qty' => 100,
            'image_url' => 'https://example.com/images/rau-muong.jpg',
        ]);

        $caChua = Product::create([
            'farmer_id' => $farmer1->id,
            'category_id' => $categories['Vegetables']->id,
            'name' => 'Cà chua bi',
            'description' => 'Cà chua bi ngọt, không thuốc trừ sâu.',
            'price' => 35000.0,
            'stock_qty' => 50,
            'image_url' => 'https://example.com/images/ca-chua-bi.jpg',
        ]);

        $xoai = Product::create([
            'farmer_id' => $farmer2->id,
            'category_id' => $categories['Fruits']->id,
            'name' => 'Xoài cát Hòa Lộc',
            'description' => 'Xoài chín cây, ngọt đậm.',
            'price' => 60000.0,
            'stock_qty' => 30,
            'image_url' => 'https://example.com/images/xoai-cat.jpg',
        ]);

        // ===== 6. Wishlists & Follows =====
        Wishlist::create([
            'customer_id' => $customer1->id,
            'product_id' => $xoai->id,
        ]);

        FarmerFollow::create([
            'customer_id' => $customer1->id,
            'farmer_id' => $farmer1->id,
        ]);

        // ===== 7. Orders (Cart & Pending Order) =====
        // Giỏ hàng (CART) của customer1
        $cartOrder = Order::create([
            'customer_id' => $customer1->id,
            'status' => 'CART',
        ]);

        OrderItem::create([
            'order_id' => $cartOrder->id,
            'product_id' => $caChua->id,
            'quantity' => 3,
        ]);

        // Đơn hàng đã đặt (PENDING) của customer1 từ farmer1
        $pendingOrder = Order::create([
            'customer_id' => $customer1->id,
            'farmer_id' => $farmer1->id,
            'delivery_address' => $customer1->address,
            'status' => 'PENDING',
            'total_price' => $rauMuong->price * 2,
        ]);

        OrderItem::create([
            'order_id' => $pendingOrder->id,
            'product_id' => $rauMuong->id,
            'product_name' => $rauMuong->name,
            'unit_price' => $rauMuong->price,
            'quantity' => 2,
            'line_total' => $rauMuong->price * 2,
        ]);
    }
}
