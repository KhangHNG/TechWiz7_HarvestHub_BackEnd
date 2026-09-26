<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private NotificationService $notifications) {}

    public function getOrders(Request $request)
    {
        $query = Order::query()->with('items');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('delivery_address', 'LIKE', "%{$keyword}%")
                    ->orWhere('status', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('all')) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
    }

    public function createOrder(array $data)
    {
        $order = DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $order = Order::create($data);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'unit_price' => $item['unit_price'],
                    'quantity' => (int) $item['quantity'],
                    'line_total' => $item['line_total'],
                ]);
            }

            if (! isset($data['total_price']) && $items) {
                $order->update([
                    'total_price' => $order->items()->sum('line_total'),
                ]);
            }

            return $order->fresh('items');
        });

        $this->notifications->orderCreated($order);

        return $order;
    }

    public function updateOrder(Order $order, array $data)
    {
        $previousStatus = $order->status;

        $order = DB::transaction(function () use ($order, $data) {
            $items = $data['items'] ?? null;
            unset($data['items']);

            $order->update($data);

            if (is_array($items)) {
                $order->items()->delete();
                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['product_name'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => (int) $item['quantity'],
                        'line_total' => $item['line_total'],
                    ]);
                }
            }

            return $order->fresh('items');
        });

        if (array_key_exists('status', $data) && $order->status !== $previousStatus) {
            $this->notifications->orderStatusChanged($order, $previousStatus);
        }

        return $order;
    }

    public function deleteOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->items()->delete();

            return $order->delete();
        });
    }
}
