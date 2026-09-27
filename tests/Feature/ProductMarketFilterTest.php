<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMarketFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_filtered_by_the_farmers_market(): void
    {
        $category = Category::query()->create(['name' => 'Rau']);
        $first = $this->productAt('Chợ Một', 'Rau muống', $category);
        $second = $this->productAt('Chợ Hai', 'Cà chua', $category);

        $this->getJson('/api/products?market_id='.$first['market']->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Rau muống')
            ->assertJsonPath('data.0.farmer.market.id', $first['market']->id)
            ->assertJsonPath('data.0.farmer.market.name', 'Chợ Một');

        $this->getJson('/api/products?market_id='.$second['market']->id)
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.name', 'Cà chua')
            ->assertJsonPath('data.0.farmer.market.id', $second['market']->id);
    }

    /**
     * @return array{market: Market, product: Product}
     */
    private function productAt(string $marketName, string $productName, Category $category): array
    {
        $market = Market::query()->create([
            'name' => $marketName,
            'address' => $marketName,
            'is_active' => true,
        ]);
        $user = User::query()->create([
            'full_name' => $marketName,
            'email' => strtolower(str_replace(' ', '', $marketName)).'@example.com',
            'phone' => $marketName === 'Chợ Một' ? '0900000001' : '0900000002',
            'password_hash' => 'secret',
            'address' => $marketName,
            'role' => 'FARMER',
            'email_verified_at' => now(),
        ]);
        $farmer = Farmer::query()->create([
            'user_id' => $user->id,
            'market_id' => $market->id,
            'business_name' => 'Vườn '.$marketName,
        ]);
        $product = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => $productName,
            'price' => 10000,
            'stock_qty' => 5,
        ]);

        return ['market' => $market, 'product' => $product];
    }
}
