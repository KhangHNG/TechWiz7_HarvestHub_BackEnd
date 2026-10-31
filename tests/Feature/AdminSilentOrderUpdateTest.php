<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminSilentOrderUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_order_edits_do_not_notify_customer_or_farmer(): void
    {
        $admin = $this->user('ADMIN', 'admin@example.com', '0900000000');
        [$farmerOwner, $farmer] = $this->farm('Garden A', 'farmer@example.com', '0900000001');
        $customer = $this->user('CUSTOMER', 'customer@example.com', '0900000002');
        $product = Product::query()->create([
            'farmer_id' => $farmer->id,
            'category_id' => Category::query()->firstOrCreate(['name' => 'Rau'])->id,
            'name' => 'Mustard greens',
            'price' => 10000,
            'stock_qty' => 25,
        ]);

        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);

        $this->actingAs($admin);

        $order = $orderService->createOrder([
            'customer_id' => $customer->id,
            'farmer_id' => $farmer->id,
            'status' => 'CART',
            'delivery_address' => '1 Street A',
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => 10000,
                    'quantity' => 10,
                    'line_total' => 100000,
                ],
            ],
        ], false);

        $order = $orderService->updateOrder($order, ['status' => 'PENDING'], false);

        $this->assertSame('PENDING', $order->status);
        $this->assertSame(15, $product->fresh()->stock_qty);
        $this->assertSame(0, UserNotification::query()->count());

        foreach (['CONFIRMED', 'READY_FOR_PICKUP', 'COMPLETED', 'CANCELLED'] as $status) {
            $order = $orderService->updateOrder($order, ['status' => $status], false);
            $this->assertSame($status, $order->status);
            $this->assertSame(0, UserNotification::query()->count());
        }

        // Even if updateOrder is called without $notify = false, actingAs($admin) suppresses notifications
        $order2 = $orderService->createOrder([
            'customer_id' => $customer->id,
            'farmer_id' => $farmer->id,
            'status' => 'CART',
            'delivery_address' => '1 Street A',
            'items' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => 10000,
                    'quantity' => 2,
                    'line_total' => 20000,
                ],
            ],
        ]);
        $order2 = $orderService->updateOrder($order2, ['status' => 'PENDING']);
        $order2 = $orderService->updateOrder($order2, ['status' => 'CONFIRMED']);

        $this->assertSame('CONFIRMED', $order2->status);
        $this->assertSame(0, UserNotification::query()->count());

        // Farmer and Customer API queries immediately see the updated status
        $this->withToken(JWTAuth::fromUser($farmerOwner))
            ->getJson('/api/orders?farmer_id='.$farmer->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $order2->id, 'status' => 'CONFIRMED']);

        $this->withToken(JWTAuth::fromUser($customer))
            ->getJson('/api/orders?customer_id='.$customer->id)
            ->assertOk()
            ->assertJsonFragment(['id' => $order2->id, 'status' => 'CONFIRMED']);
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
            'address' => '1 Street A',
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}
