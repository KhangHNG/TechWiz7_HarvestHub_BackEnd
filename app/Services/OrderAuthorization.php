<?php

namespace App\Services;

use App\Models\Farmer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class OrderAuthorization
{
    /**
     * @var array<int, string>
     */
    private const CUSTOMER_CANCEL_FROM = ['CART', 'PENDING', 'CONFIRMED', 'READY_FOR_PICKUP'];

    /**
     * @var array<string, array<int, string>>
     */
    private const FARMER_TRANSITIONS = [
        'PENDING' => ['CONFIRMED', 'CANCELLED'],
        'CONFIRMED' => ['READY_FOR_PICKUP', 'CANCELLED'],
        'READY_FOR_PICKUP' => ['COMPLETED', 'CANCELLED'],
    ];

    public function scopeIndex(User $user, Request $request): void
    {
        if ($user->role === 'CUSTOMER') {
            $request->merge(['customer_id' => $user->id]);

            return;
        }

        if ($user->role === 'FARMER') {
            $request->merge(['farmer_id' => $this->farmerProfile($user)->id]);
        }
    }

    public function assertCanView(User $user, Order $order): void
    {
        if ($user->role === 'ADMIN') {
            return;
        }

        if ($user->role === 'CUSTOMER' && (int) $order->customer_id === (int) $user->id) {
            return;
        }

        if ($user->role === 'FARMER' && (int) $order->farmer_id === (int) $this->farmerProfile($user)->id) {
            return;
        }

        $this->deny('You do not have permission to view this order.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertCanUpdate(User $user, Order $order, array $data): void
    {
        if ($user->role === 'ADMIN') {
            $this->deny('Admins cannot update orders via the API.');
        }

        if ($user->role === 'CUSTOMER') {
            if ((int) $order->customer_id !== (int) $user->id) {
                $this->deny('You do not have permission to update this order.');
            }

            $this->assertCustomerUpdate($order, $data);

            return;
        }

        if ($user->role === 'FARMER') {
            if ((int) $order->farmer_id !== (int) $this->farmerProfile($user)->id) {
                $this->deny('You do not have permission to update this order.');
            }

            $this->assertFarmerUpdate($order, $data);

            return;
        }

        $this->deny('You do not have permission to perform this action.');
    }

    public function assertCanDelete(User $user, Order $order): void
    {
        if ($user->role === 'ADMIN') {
            $this->deny('Admins cannot delete orders via the API.');
        }

        if ($user->role !== 'CUSTOMER' || (int) $order->customer_id !== (int) $user->id) {
            $this->deny('You do not have permission to delete this order.');
        }

        if ($order->status !== 'CART') {
            $this->deny('Placed orders must be cancelled instead of deleted.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertCustomerUpdate(Order $order, array $data): void
    {
        $contentKeys = array_intersect(array_keys($data), ['delivery_address', 'city', 'district', 'ward', 'items', 'total_price']);
        if ($contentKeys !== [] && $order->status !== 'CART') {
            $this->deny('Only cart orders can be edited.');
        }

        if (! array_key_exists('status', $data)) {
            return;
        }

        $current = (string) $order->status;
        $next = (string) $data['status'];
        if ($next === $current) {
            return;
        }

        if ($current === 'CART' && $next === 'PENDING') {
            return;
        }

        if ($next === 'CANCELLED' && in_array($current, self::CUSTOMER_CANCEL_FROM, true)) {
            return;
        }

        $this->deny('You cannot change the order to this status.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertFarmerUpdate(Order $order, array $data): void
    {
        $extra = array_diff(array_keys($data), ['status']);
        if ($extra !== []) {
            $this->deny('Farmers may only update the order status.');
        }

        if (! array_key_exists('status', $data)) {
            return;
        }

        $current = (string) $order->status;
        $next = (string) $data['status'];
        if ($next === $current) {
            return;
        }

        $allowed = self::FARMER_TRANSITIONS[$current] ?? [];
        if (! in_array($next, $allowed, true)) {
            $this->deny('You cannot change the order to this status.');
        }
    }

    private function farmerProfile(User $user): Farmer
    {
        $farmer = $user->farmer;

        if (! $farmer) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Farmer profile not found.',
            ], 404));
        }

        return $farmer;
    }

    private function deny(string $message): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $message,
        ], 403));
    }
}
