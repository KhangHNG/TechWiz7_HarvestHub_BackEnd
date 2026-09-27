<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::query()
            ->with(['customer', 'farmer.user'])
            ->where('status', '!=', 'CART')
            ->orderBy('id')
            ->get();

        foreach ($orders as $order) {
            [$user, $type, $title, $body, $read] = match ($order->status) {
                'PENDING' => [
                    $order->farmer?->user,
                    'order_pending',
                    'New order',
                    'You have a new order waiting for confirmation.',
                    false,
                ],
                'CONFIRMED' => [
                    $order->customer,
                    'order_confirmed',
                    'Order confirmed',
                    'The farmer confirmed your order.',
                    true,
                ],
                'READY_FOR_PICKUP' => [
                    $order->customer,
                    'order_ready',
                    'Ready for pickup',
                    'Your order is ready for pickup.',
                    false,
                ],
                'COMPLETED' => [
                    $order->customer,
                    'order_completed',
                    'Order completed',
                    'Your order has been completed.',
                    true,
                ],
                'CANCELLED' => [
                    $order->customer,
                    'order_cancelled',
                    'Order cancelled',
                    'The farmer cancelled your order.',
                    false,
                ],
                default => [null, null, null, null, false],
            };

            if ($user === null || $type === null) {
                continue;
            }

            UserNotification::query()->create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => [
                    'type' => $type,
                    'order_id' => (string) $order->id,
                ],
                'read_at' => $read ? now()->subHour() : null,
            ]);
        }
    }
}
