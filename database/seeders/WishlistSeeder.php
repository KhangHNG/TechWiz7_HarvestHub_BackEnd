<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::query()->where('role', 'CUSTOMER')->orderBy('id')->get();
        $products = Product::query()->orderBy('id')->get();

        foreach ($customers as $index => $customer) {
            Wishlist::create([
                'customer_id' => $customer->id,
                'product_id' => $products[$index]->id,
            ]);
        }
    }
}
