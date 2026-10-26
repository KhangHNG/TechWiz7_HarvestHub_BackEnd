<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Product;
use App\Models\User;
use App\Services\Ai\AiToolResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiToolResolverTest extends TestCase
{
    use RefreshDatabase;

    private AiToolResolver $resolver;
    private Farmer $farmer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new AiToolResolver;

        $user = User::query()->create([
            'full_name' => 'Farm Owner',
            'email' => 'owner@example.com',
            'phone' => '0900000001',
            'password_hash' => 'secret',
            'address' => '1 Street A',
            'role' => 'FARMER',
            'email_verified_at' => now(),
        ]);
        $market = Market::create([
            'name' => 'Test Market',
            'address' => '123 Market St',
            'city' => 'City',
            'ward' => 'Ward',
            'operating_hours' => '8AM - 5PM',
        ]);

        $this->farmer = Farmer::create([
            'user_id' => $user->id,
            'business_name' => 'Han River Produce',
            'market_id' => $market->id,
        ]);

        $vegCategory = Category::create([
            'name' => 'Vegetables',
            'slug' => 'vegetables',
        ]);

        $fruitCategory = Category::create([
            'name' => 'Fruits',
            'slug' => 'fruits',
        ]);

        Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $vegCategory->id,
            'name' => 'Cherry tomato',
            'slug' => 'cherry-tomato',
            'price' => 1.90,
            'stock_qty' => 15,
            'unit' => 'kg',
        ]);

        Product::create([
            'farmer_id' => $this->farmer->id,
            'category_id' => $fruitCategory->id,
            'name' => 'Hoa Loc mango',
            'slug' => 'hoa-loc-mango',
            'price' => 2.60,
            'stock_qty' => 25,
            'unit' => 'kg',
        ]);
    }

    public function test_list_products_matches_category_plural_and_singular(): void
    {
        $fruitResult = $this->resolver->execute('list_products', ['keyword' => 'fruits']);
        $this->assertSame('success', $fruitResult['status']);
        $this->assertCount(1, $fruitResult['data']);
        $this->assertSame('Hoa Loc mango', $fruitResult['data'][0]['product_name']);

        $vegResult = $this->resolver->execute('list_products', ['keyword' => 'vegetable']);
        $this->assertSame('success', $vegResult['status']);
        $this->assertCount(1, $vegResult['data']);
        $this->assertSame('Cherry tomato', $vegResult['data'][0]['product_name']);
    }

    public function test_list_products_matches_vitamin_a_nutrient(): void
    {
        $result = $this->resolver->execute('list_products', ['keyword' => 'vitamin A']);
        $this->assertSame('success', $result['status']);
        $names = array_column($result['data'], 'product_name');
        $this->assertContains('Cherry tomato', $names);
        $this->assertContains('Hoa Loc mango', $names);
    }

    public function test_list_products_cleans_vietnamese_and_english_farm_prefixes(): void
    {
        $result = $this->resolver->execute('list_products', ['farmer' => 'nông trại Han River']);
        $this->assertSame('success', $result['status']);
        $this->assertCount(2, $result['data']);

        $resultEnglish = $this->resolver->execute('list_products', ['farmer' => 'Han River Farm']);
        $this->assertSame('success', $resultEnglish['status']);
        $this->assertCount(2, $resultEnglish['data']);
    }
}
