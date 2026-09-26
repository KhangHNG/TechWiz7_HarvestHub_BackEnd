<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    use SeedsAudit;

    public function run(): void
    {
        $customers = User::query()->where('role', 'CUSTOMER')->orderBy('id')->get();
        $products = Product::query()->orderBy('id')->get();

        foreach ($customers as $customerIndex => $customer) {
            for ($slot = 0; $slot < 3; $slot++) {
                $product = $products[($customerIndex * 3 + $slot) % $products->count()];

                $this->createAudited(Wishlist::class, [
                    'customer_id' => $customer->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
