<?php

namespace App\Services;

use App\Exceptions\FarmerNotAcceptingOrdersException;
use App\Exceptions\InsufficientStockException;
use App\Models\Farmer;
use App\Models\FarmerWalletTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * @var array<int, Order>
     */
    private array $placedOrders = [];

    public function __construct(private NotificationService $notifications) {}

    /**
     * @return array<int, Order>
     */
    public function placedOrders(): array
    {
        return $this->placedOrders;
    }

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
        $this->assertFarmersAcceptingOrders(collect($data['items'] ?? [])->pluck('product_id'));

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
        $nextStatus = array_key_exists('status', $data) ? (string) $data['status'] : (string) $previousStatus;

        if ($previousStatus === 'CART' && $nextStatus === 'PENDING') {
            $productIds = array_key_exists('items', $data)
                ? collect($data['items'])->pluck('product_id')
                : $order->items()->pluck('product_id');
            $this->assertFarmersAcceptingOrders($productIds);
        }

        $stockChanges = [];
        $placedOrders = [];

        $order = DB::transaction(function () use ($order, $data, $previousStatus, &$stockChanges, &$placedOrders) {
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

            $order = $order->fresh('items');

            if ($previousStatus === 'CART' && $order->status === 'PENDING') {
                $placedOrders = $this->splitPendingByFarmer($order, $stockChanges);
                $order = $placedOrders[0];
            }

            if (
                $order->status === 'CANCELLED'
                && in_array($previousStatus, ['PENDING', 'CONFIRMED', 'READY_FOR_PICKUP'], true)
            ) {
                $stockChanges = $this->adjustStock($order, 1);
            }

            if ($previousStatus !== 'COMPLETED' && $order->status === 'COMPLETED' && $order->completed_at === null) {
                $order->update(['completed_at' => now()]);
                $this->creditBankTransfer($order);
                $order = $order->fresh('items');
            }

            if ($placedOrders === []) {
                $placedOrders = [$order->fresh('items')];
            }

            return $order;
        });

        $this->placedOrders = $placedOrders;

        foreach ($stockChanges as [$product, $previous, $current]) {
            $this->notifications->stockChanged($product, $previous, $current);
        }

        if ($previousStatus === 'CART' && $order->status === 'PENDING') {
            foreach ($placedOrders as $placed) {
                $this->notifications->orderStatusChanged($placed, 'CART');
            }
        } elseif (array_key_exists('status', $data) && $order->status !== $previousStatus) {
            $this->notifications->orderStatusChanged($order, $previousStatus);
        }

        return $order;
    }

    /**
     * @param  array<int, array{0: Product, 1: int, 2: int}>  $stockChanges
     * @return array<int, Order>
     */
    private function splitPendingByFarmer(Order $order, array &$stockChanges): array
    {
        $order->load(['items.product']);
        $groups = $order->items->groupBy(function (OrderItem $item): int {
            $product = $item->product;

            if (! $product) {
                throw new InsufficientStockException('Sản phẩm trong đơn không còn để cập nhật kho.');
            }

            return (int) $product->farmer_id;
        });

        if ($order->items->isEmpty()) {
            throw new InsufficientStockException('Sản phẩm trong đơn không còn để cập nhật kho.');
        }

        $placed = [];
        $firstFarmerId = (int) $groups->keys()->first();

        foreach ($groups as $farmerId => $items) {
            $farmerId = (int) $farmerId;

            if ($farmerId === $firstFarmerId) {
                continue;
            }

            $sibling = Order::query()->create([
                'customer_id' => $order->customer_id,
                'farmer_id' => $farmerId,
                'delivery_address' => $order->delivery_address,
                'status' => 'PENDING',
                'payment_method' => $order->payment_method,
                'total_price' => $items->sum('line_total'),
            ]);

            foreach ($items as $item) {
                $item->update(['order_id' => $sibling->id]);
            }

            $sibling = $sibling->fresh('items');
            $stockChanges = array_merge($stockChanges, $this->adjustStock($sibling, -1));
            $placed[] = $sibling;
        }

        $kept = $groups->get($firstFarmerId) ?? $groups->first();
        $order->update([
            'farmer_id' => $firstFarmerId,
            'total_price' => $kept->sum('line_total'),
        ]);
        $current = $order->fresh('items');
        $stockChanges = array_merge($stockChanges, $this->adjustStock($current, -1));

        return array_merge([$current], $placed);
    }

    private function creditBankTransfer(Order $order): void
    {
        if ($order->payment_method !== 'BANK_TRANSFER' || $order->farmer_id === null) {
            return;
        }

        FarmerWalletTransaction::query()->create([
            'farmer_id' => $order->farmer_id,
            'order_id' => $order->id,
            'amount' => $order->total_price,
            'type' => 'credit',
        ]);
    }

    /**
     * @return array<int, array{0: Product, 1: int, 2: int}>
     */
    private function adjustStock(Order $order, int $direction): array
    {
        $changes = [];

        foreach ($order->items as $item) {
            $product = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
            $quantity = (int) $item->quantity;

            if (! $product) {
                throw new InsufficientStockException('Sản phẩm trong đơn không còn để cập nhật kho.');
            }

            $previous = (int) $product->stock_qty;
            if ($direction < 0 && $previous < $quantity) {
                throw new InsufficientStockException('Sản phẩm '.$product->name.' không đủ số lượng trong kho.');
            }

            $current = $direction < 0 ? $previous - $quantity : $previous + $quantity;
            $product->update(['stock_qty' => $current]);
            $changes[] = [$product, $previous, $current];
        }

        return $changes;
    }

    /**
     * @param  Collection<int, mixed>|array<int, mixed>  $productIds
     */
    private function assertFarmersAcceptingOrders(mixed $productIds): void
    {
        $ids = collect($productIds)->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return;
        }

        $names = Product::query()
            ->whereIn('id', $ids)
            ->with('farmer')
            ->get()
            ->map(fn (Product $product) => $product->farmer)
            ->filter(fn (?Farmer $farmer) => $farmer && ! $farmer->is_accepting_orders)
            ->pluck('business_name')
            ->unique()
            ->sort()
            ->values();

        if ($names->isEmpty()) {
            return;
        }

        $label = $names->count() === 1 ? $names->first() : $names->join(', ');

        throw new FarmerNotAcceptingOrdersException('Nông trại '.$label.' đang tạm ngừng nhận đơn.');
    }

    public function deleteOrder(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->items()->delete();

            return $order->delete();
        });
    }
}
