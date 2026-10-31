<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Product;
use App\Models\User;
use App\Support\SeedImages;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FarmerSeeder;
use Database\Seeders\MarketSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedImageMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_mapped_seed_image_exists_on_disk_and_is_valid(): void
    {
        foreach (SeedImages::CATEGORIES as $category => $path) {
            $this->assertFileExists(public_path($path), "Missing category image for {$category}: {$path}");
            $this->assertNotFalse(getimagesize(public_path($path)), "Invalid category image file: {$path}");
        }

        foreach (SeedImages::FARM_COVERS as $farm => $path) {
            $this->assertFileExists(public_path($path), "Missing farm cover for {$farm}: {$path}");
            $this->assertNotFalse(getimagesize(public_path($path)), "Invalid farm cover file: {$path}");
        }

        foreach (SeedImages::PRODUCTS as $product => $paths) {
            $this->assertNotEmpty($paths, "Product {$product} has no images mapped");
            foreach ($paths as $path) {
                $this->assertFileExists(public_path($path), "Missing product image for {$product}: {$path}");
                $this->assertNotFalse(getimagesize(public_path($path)), "Invalid product image file: {$path}");
            }
        }
    }

    public function test_seeded_products_and_farms_return_full_matching_image_links(): void
    {
        $this->seed([
            UserSeeder::class,
            MarketSeeder::class,
            FarmerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        $products = $this->getJson('/api/products?per_page=100')->assertOk()->json('data');
        $this->assertCount(57, $products);

        foreach ($products as $row) {
            $expected = array_map(fn (string $p): string => url($p), SeedImages::forProduct($row['name']) ?? []);
            $this->assertNotEmpty($expected, "Unmapped seeded product {$row['name']}");
            $this->assertSame($expected, $row['image_url'], "Image mismatch for product {$row['name']}");
            $this->assertSame($expected[0], $row['thumbnail_url']);
        }

        $farmers = $this->getJson('/api/farmers?all=1')->assertOk()->json('data');
        $this->assertCount(12, $farmers);

        foreach ($farmers as $row) {
            $expectedCover = SeedImages::forFarmCover($row['business_name']);
            $this->assertNotNull($expectedCover, "Unmapped seeded farm {$row['business_name']}");
            $this->assertSame(url($expectedCover), $row['cover_url'], "Cover mismatch for farm {$row['business_name']}");
        }
    }

    public function test_migration_replaces_wikimedia_and_emulator_sample_images_while_keeping_real_uploads(): void
    {
        $owner = User::query()->create([
            'full_name' => 'Test Farmer',
            'email' => 'farmer-seed-test@example.com',
            'phone' => '0909999999',
            'password_hash' => 'secret',
            'address' => '1 Le Loi',
            'role' => 'FARMER',
            'email_verified_at' => now(),
        ]);
        $market = Market::query()->create([
            'name' => 'Ben Thanh Market',
            'address' => 'District 1',
            'latitude' => 10.77,
            'longitude' => 106.69,
            'operating_hours' => '06:00 - 18:00',
            'is_active' => true,
        ]);
        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'market_id' => $market->id,
            'business_name' => 'Green Leaf Farm',
            'cover_url' => 'https://upload.wikimedia.org/wikipedia/commons/thumb/c/cb/Ipomoea_aquatica_kz01.jpg/960px-Ipomoea_aquatica_kz01.jpg',
            'is_accepting_orders' => true,
        ]);
        $category = Category::query()->create([
            'name' => 'Vegetables',
            'image_url' => null,
        ]);

        $wikimediaProduct = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Hoa Loc mango',
            'description' => 'Ripe mangoes',
            'price' => 2.6,
            'stock_qty' => 10,
            'image_url' => ['https://upload.wikimedia.org/wikipedia/commons/thumb/a/af/Mango.jpg/960px-Mango.jpg'],
        ]);

        $emulatorSweetWater = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Sweet Water',
            'description' => 'Sdasdasd',
            'price' => 5.0,
            'stock_qty' => 17,
            'image_url' => ['https://res.cloudinary.com/dn6rs5y6t/image/upload/v1790646323/products/zv6xyv4shgyuoprfx4nu.jpg'],
        ]);

        $realCustomUpload = 'https://res.cloudinary.com/dn6rs5y6t/image/upload/v1799999999/products/real_farmer_photo.jpg';
        $customUploadedProduct = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => $category->id,
            'name' => 'Tomato',
            'description' => 'Farmer custom photo',
            'price' => 0.8,
            'stock_qty' => 20,
            'image_url' => [$realCustomUpload],
        ]);

        (require database_path('migrations/2026_10_31_202500_replace_product_and_farm_images_from_frontend.php'))->up();

        $this->assertSame('seed/categories/vegetables.webp', $category->fresh()->image_url);
        $this->assertSame('seed/farms/green_valley_cover.webp', $farmer->fresh()->cover_url);
        $this->assertSame(['seed/products/hoa-loc-mango.jpg'], $wikimediaProduct->fresh()->image_url);
        $this->assertSame(
            ['seed/products/sweet-water-1.jpg', 'seed/products/sweet-water-2.jpg'],
            $emulatorSweetWater->fresh()->image_url,
        );
        $this->assertSame(
            'Chilled fruit-flavored sodas bottled for the market stall.',
            $emulatorSweetWater->fresh()->description,
        );
        $this->assertSame([$realCustomUpload], $customUploadedProduct->fresh()->image_url);
    }

    public function test_order_item_resource_provides_valid_image_url_even_when_product_is_missing(): void
    {
        $this->seed([
            UserSeeder::class,
            MarketSeeder::class,
            FarmerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        $user = User::query()->where('role', 'CUSTOMER')->first();
        $farmer = Farmer::query()->first();
        $product = Product::query()->first();

        $order = \App\Models\Order::query()->create([
            'customer_id' => $user->id,
            'farmer_id' => $farmer->id,
            'delivery_address' => '123 Test St',
            'city' => 'Hanoi',
            'district' => 'Dong Da',
            'ward' => 'Lang Ha',
            'status' => 'PENDING',
            'payment_method' => 'COD',
            'total_price' => 10.0,
        ]);

        $deletedProduct = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => $product->category_id,
            'name' => 'Organic Fresh Strawberries',
            'description' => 'Test strawberries',
            'price' => 4.5,
            'stock_qty' => 5,
            'image_url' => [],
        ]);

        $itemWithProduct = \App\Models\OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 2,
            'line_total' => $product->price * 2,
        ]);

        $itemWithoutProduct = \App\Models\OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $deletedProduct->id,
            'product_name' => $deletedProduct->name,
            'unit_price' => 4.5,
            'quantity' => 1,
            'line_total' => 4.5,
        ]);

        $deletedProduct->delete();

        $itemWithProduct->load('product');
        $resourceWithProduct = (new \App\Http\Resources\OrderItemResource($itemWithProduct))->toArray(request());
        $this->assertNotEmpty($resourceWithProduct['image_url']);
        $this->assertStringStartsWith('http', $resourceWithProduct['image_url']);

        $resourceWithoutProduct = (new \App\Http\Resources\OrderItemResource($itemWithoutProduct))->toArray(request());
        $this->assertNotEmpty($resourceWithoutProduct['image_url']);
        $this->assertStringStartsWith('http', $resourceWithoutProduct['image_url']);
        $this->assertStringContainsString('strawberries.webp', $resourceWithoutProduct['image_url']);
    }
}

