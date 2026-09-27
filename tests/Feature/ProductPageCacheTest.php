<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductPageCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_product_page_is_not_queried_again_until_data_changes(): void
    {
        $this->seedCatalog();

        $first = $this->getJson('/api/products?per_page=1&page=1');
        $first->assertOk();
        $first->assertJsonPath('meta.current_page', 1);
        $first->assertJsonPath('meta.last_page', 3);
        $first->assertJsonPath('data.0.name', 'New greens');
        $first->assertJsonPath('data.0.farmer.business_name', 'Garden A');
        $first->assertJsonPath('data.0.farmer.market.name', 'Market X');
        $first->assertJsonPath('data.0.category.name', 'Rau');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $cached = $this->getJson('/api/products?per_page=1&page=1');
        $cached->assertOk();
        $cached->assertJsonPath('data.0.name', 'New greens');
        $cached->assertJsonPath('data.0.farmer.business_name', 'Garden A');
        $cached->assertJsonPath('data.0.category.name', 'Rau');
        $this->assertSame([], DB::getQueryLog());

        $pageTwo = $this->getJson('/api/products?per_page=1&page=2');
        $pageTwo->assertOk();
        $pageTwo->assertJsonPath('data.0.name', 'Out of stock');

        DB::flushQueryLog();
        $this->getJson('/api/products?per_page=1&page=1')->assertOk();
        $this->getJson('/api/products?per_page=1&page=2')->assertOk();
        $this->assertSame([], DB::getQueryLog());

        Product::query()->where('name', 'New greens')->firstOrFail()->update(['stock_qty' => 1]);

        DB::flushQueryLog();
        $refreshed = $this->getJson('/api/products?per_page=1&page=1');
        $refreshed->assertOk();
        $refreshed->assertJsonPath('data.0.stock_qty', 1);
        $this->assertNotSame([], DB::getQueryLog());
    }

    public function test_in_stock_filter_skips_empty_products(): void
    {
        $this->seedCatalog();

        $response = $this->getJson('/api/products?in_stock=1&per_page=10');

        $response->assertOk();
        $response->assertJsonPath('meta.total', 2);
        $response->assertJsonPath('data.0.name', 'New greens');
        $response->assertJsonPath('data.1.name', 'Tomato');
    }

    private function seedCatalog(): void
    {
        $now = now();

        DB::table('users')->insert([
            'id' => 1,
            'full_name' => 'Farmer A',
            'email' => 'farmer@example.com',
            'phone' => '0900000001',
            'password_hash' => 'secret',
            'address' => '1 Street A',
            'role' => 'FARMER',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('markets')->insert([
            'id' => 1,
            'name' => 'Market X',
            'address' => 'District 1',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('farmers')->insert([
            'id' => 1,
            'user_id' => 1,
            'market_id' => 1,
            'business_name' => 'Garden A',
            'rating' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('categories')->insert([
            'id' => 1,
            'name' => 'Rau',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ([
            ['Tomato', 4, $now->copy()->subHour()],
            ['Out of stock', 0, $now->copy()->subMinutes(30)],
            ['New greens', 5, $now],
        ] as [$name, $stock, $createdAt]) {
            $product = Product::query()->create([
                'farmer_id' => 1,
                'category_id' => 1,
                'name' => $name,
                'price' => 10000,
                'stock_qty' => $stock,
            ]);
            DB::table('products')->where('id', $product->id)->update([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
