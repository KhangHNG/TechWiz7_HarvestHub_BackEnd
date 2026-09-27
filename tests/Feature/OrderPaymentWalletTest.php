<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\FarmerWalletTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderPaymentWalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_method_is_only_accepted_when_the_cart_is_placed(): void
    {
        [$owner, $farmer] = $this->farm('Vườn A', 'a@example.com', '0900000001');
        $customer = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $product = $this->product($farmer, 'Cải', 10000);
        $token = $this->token($customer);

        $cartId = $this->withToken($token)
            ->postJson('/api/orders', $this->cartPayload([$product]))
            ->assertCreated()
            ->assertJsonPath('data.payment_method', 'COD')
            ->json('data.id');

        $this->withToken($token)
            ->putJson('/api/orders/'.$cartId, [
                'payment_method' => 'BANK_TRANSFER',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.payment_method.0', 'Chỉ được chọn phương thức thanh toán khi đặt đơn.');

        $this->withToken($token)
            ->putJson('/api/orders/'.$cartId, [
                'status' => 'PENDING',
                'delivery_address' => '1 Đường A',
                'payment_method' => 'BANK_TRANSFER',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_method', 'BANK_TRANSFER');

        $this->withToken($this->token($owner))
            ->putJson('/api/orders/'.$cartId, [
                'status' => 'CONFIRMED',
                'payment_method' => 'COD',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.payment_method.0', 'Chỉ được chọn phương thức thanh toán khi đặt đơn.');
    }

    public function test_bank_transfer_is_copied_when_split_and_credited_once(): void
    {
        [$ownerA, $farmerA] = $this->farm('Vườn A', 'a@example.com', '0900000001');
        [$ownerB, $farmerB] = $this->farm('Vườn B', 'b@example.com', '0900000002');
        $customer = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $productA = $this->product($farmerA, 'Cải', 10000);
        $productB = $this->product($farmerB, 'Cà rốt', 20000);

        $this->getJson('/api/farmer/wallet')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Token bị thiếu hoặc không thể giải mã.');

        $placed = $this->withToken($this->token($customer))
            ->postJson('/api/orders', $this->cartPayload([$productA, $productB]))
            ->assertCreated()
            ->json('data.id');

        $this->withToken($this->token($customer))
            ->putJson('/api/orders/'.$placed, [
                'status' => 'PENDING',
                'delivery_address' => '1 Đường A',
                'payment_method' => 'BANK_TRANSFER',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_method', 'BANK_TRANSFER');

        $orders = Order::query()->where('status', 'PENDING')->get();
        $this->assertCount(2, $orders);
        $this->assertTrue($orders->every(fn (Order $order) => $order->payment_method === 'BANK_TRANSFER'));

        $orderA = $orders->firstWhere('farmer_id', $farmerA->id);
        $this->complete($ownerA, $orderA);

        $this->assertDatabaseCount('farmer_wallet_transactions', 1);
        $this->assertDatabaseHas('farmer_wallet_transactions', [
            'farmer_id' => $farmerA->id,
            'order_id' => $orderA->id,
            'amount' => $orderA->fresh()->total_price,
            'type' => 'credit',
        ]);

        $debitOrder = Order::query()->create([
            'customer_id' => $customer->id,
            'farmer_id' => $farmerA->id,
            'status' => 'CANCELLED',
            'payment_method' => 'COD',
            'total_price' => 4000,
        ]);
        FarmerWalletTransaction::query()->create([
            'farmer_id' => $farmerA->id,
            'order_id' => $debitOrder->id,
            'amount' => 4000,
            'type' => 'debit',
        ]);

        $wallet = $this->withToken($this->token($ownerA))
            ->getJson('/api/farmer/wallet')
            ->assertOk();
        $this->assertEquals(
            (float) $orderA->fresh()->total_price - 4000,
            $wallet->json('data.balance'),
        );

        $this->withToken($this->token($ownerB))
            ->getJson('/api/farmer/wallet')
            ->assertOk()
            ->assertJsonPath('data.balance', 0);
    }

    public function test_cod_completion_does_not_credit_the_wallet(): void
    {
        [$owner, $farmer] = $this->farm('Vườn A', 'a@example.com', '0900000001');
        $customer = $this->user('CUSTOMER', 'khach@example.com', '0900000003');
        $product = $this->product($farmer, 'Cải', 10000);

        $cartId = $this->withToken($this->token($customer))
            ->postJson('/api/orders', $this->cartPayload([$product]))
            ->assertCreated()
            ->json('data.id');

        $this->withToken($this->token($customer))
            ->putJson('/api/orders/'.$cartId, [
                'status' => 'PENDING',
                'delivery_address' => '1 Đường A',
            ])
            ->assertOk()
            ->assertJsonPath('data.payment_method', 'COD');

        $this->complete($owner, Order::query()->findOrFail($cartId));

        $this->assertDatabaseCount('farmer_wallet_transactions', 0);
        $this->withToken($this->token($owner))
            ->getJson('/api/farmer/wallet')
            ->assertOk()
            ->assertJsonPath('data.balance', 0);
    }

    private function complete(User $owner, Order $order): void
    {
        foreach (['CONFIRMED', 'READY_FOR_PICKUP', 'COMPLETED'] as $status) {
            $this->withToken($this->token($owner))
                ->putJson('/api/orders/'.$order->id, ['status' => $status])
                ->assertOk()
                ->assertJsonPath('data.status', $status);
        }
    }

    /**
     * @return array{0: User, 1: Farmer}
     */
    private function farm(string $name, string $email, string $phone): array
    {
        $owner = $this->user('FARMER', $email, $phone);
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
            'address' => '1 Đường A',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function product(Farmer $farmer, string $name, float $price): Product
    {
        return Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => Category::query()->firstOrCreate(['name' => 'Rau'])->id,
            'name' => $name,
            'price' => $price,
            'stock_qty' => 20,
        ]);
    }

    private function token(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * @param  array<int, Product>  $products
     * @return array<string, mixed>
     */
    private function cartPayload(array $products): array
    {
        $items = array_map(fn (Product $product) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price' => $product->price,
            'quantity' => 1,
            'line_total' => $product->price,
        ], $products);

        return [
            'delivery_address' => '1 Đường A',
            'status' => 'CART',
            'total_price' => array_sum(array_column($items, 'line_total')),
            'items' => $items,
        ];
    }
}
