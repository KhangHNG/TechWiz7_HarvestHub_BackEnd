<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AcceptingOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_farm_owner_can_pause_orders(): void
    {
        [$owner, $farmer] = $this->farm('Garden A');
        $other = $this->user('FARMER', 'other@example.com', '0900000002');

        $this->putJson('/api/farmers/'.$farmer->id, ['is_accepting_orders' => false])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Token is missing or could not be decoded.');

        $this->withToken($this->token($other))
            ->putJson('/api/farmers/'.$farmer->id, ['is_accepting_orders' => false])
            ->assertForbidden();

        $this->withToken($this->token($owner))
            ->putJson('/api/farmers/'.$farmer->id, ['is_accepting_orders' => false])
            ->assertOk()
            ->assertJsonPath('data.is_accepting_orders', false);

        $this->getJson('/api/farmers/'.$farmer->id)
            ->assertOk()
            ->assertJsonPath('data.is_accepting_orders', false);
    }

    public function test_paused_farm_blocks_new_carts_and_checkout_but_not_open_orders(): void
    {
        [$owner, $farmer] = $this->farm('Garden A');
        $customer = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $product = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => Category::query()->create(['name' => 'Rau'])->id,
            'name' => 'Mustard greens',
            'price' => 10000,
            'stock_qty' => 20,
        ]);
        $customerToken = $this->token($customer);

        $openCart = $this->withToken($customerToken)
            ->postJson('/api/orders', $this->cartPayload($product, 1))
            ->assertCreated()
            ->json('data.id');

        $heldCart = $this->withToken($customerToken)
            ->postJson('/api/orders', $this->cartPayload($product, 1))
            ->assertCreated()
            ->json('data.id');

        $this->withToken($customerToken)
            ->putJson('/api/orders/'.$openCart, [
                'status' => 'PENDING',
                'delivery_address' => '1 Street A',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'PENDING');

        $this->withToken($this->token($owner))
            ->putJson('/api/farmers/'.$farmer->id, ['is_accepting_orders' => false])
            ->assertOk();

        $this->getJson('/api/products/'.$product->id)
            ->assertOk()
            ->assertJsonPath('data.farmer.is_accepting_orders', false);

        $this->withToken($customerToken)
            ->putJson('/api/orders/'.$heldCart, [
                'status' => 'CART',
                'items' => [$this->line($product, 2)],
                'total_price' => 20000,
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'CART');

        $this->withToken($customerToken)
            ->putJson('/api/orders/'.$heldCart, [
                'status' => 'PENDING',
                'delivery_address' => '1 Street A',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Farm Garden A is temporarily not accepting orders.');

        $this->withToken($customerToken)
            ->postJson('/api/orders', $this->cartPayload($product, 1))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Farm Garden A is temporarily not accepting orders.');

        $this->withToken($this->token($owner))
            ->putJson('/api/orders/'.$openCart, ['status' => 'CONFIRMED'])
            ->assertOk()
            ->assertJsonPath('data.status', 'CONFIRMED');
    }

    /**
     * @return array{0: User, 1: Farmer}
     */
    private function farm(string $name): array
    {
        $owner = $this->user('FARMER', 'farmer@example.com', '0900000001');
        $farmer = Farmer::query()->create([
            'user_id' => $owner->id,
            'business_name' => $name,
            'is_accepting_orders' => true,
        ]);

        return [$owner, $farmer];
    }

    private function user(string $role, string $email, string $phone): User
    {
        return User::query()->create([
            'full_name' => $email,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => 'secret',
            'address' => '1 Street A',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function token(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    private function cartPayload(Product $product, int $quantity): array
    {
        return [
            'delivery_address' => '1 Street A',
            'status' => 'CART',
            'total_price' => $product->price * $quantity,
            'items' => [$this->line($product, $quantity)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function line(Product $product, int $quantity): array
    {
        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => $quantity,
            'line_total' => $product->price * $quantity,
        ];
    }
}
