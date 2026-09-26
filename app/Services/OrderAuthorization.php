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

        $this->deny('Bạn không có quyền xem đơn hàng này.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assertCanUpdate(User $user, Order $order, array $data): void
    {
        if ($user->role === 'ADMIN') {
            $this->deny('Admin không được sửa đơn qua API.');
        }

        if ($user->role === 'CUSTOMER') {
            if ((int) $order->customer_id !== (int) $user->id) {
                $this->deny('Bạn không có quyền sửa đơn hàng này.');
            }

            $this->assertCustomerUpdate($order, $data);

            return;
        }

        if ($user->role === 'FARMER') {
            if ((int) $order->farmer_id !== (int) $this->farmerProfile($user)->id) {
                $this->deny('Bạn không có quyền sửa đơn hàng này.');
            }

            $this->assertFarmerUpdate($order, $data);

            return;
        }

        $this->deny('Bạn không có quyền thực hiện thao tác này.');
    }

    public function assertCanDelete(User $user, Order $order): void
    {
        if ($user->role === 'ADMIN') {
            $this->deny('Admin không được xóa đơn qua API.');
        }

        if ($user->role !== 'CUSTOMER' || (int) $order->customer_id !== (int) $user->id) {
            $this->deny('Bạn không có quyền xóa đơn hàng này.');
        }

        if ($order->status !== 'CART') {
            $this->deny('Đơn đã đặt cần được hủy thay vì xóa.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertCustomerUpdate(Order $order, array $data): void
    {
        $contentKeys = array_intersect(array_keys($data), ['delivery_address', 'items', 'total_price']);
        if ($contentKeys !== [] && $order->status !== 'CART') {
            $this->deny('Chỉ đơn giỏ hàng mới được sửa thông tin.');
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

        $this->deny('Bạn không được chuyển đơn sang trạng thái này.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function assertFarmerUpdate(Order $order, array $data): void
    {
        $extra = array_diff(array_keys($data), ['status']);
        if ($extra !== []) {
            $this->deny('Nông dân chỉ được cập nhật trạng thái đơn hàng.');
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
            $this->deny('Bạn không được chuyển đơn sang trạng thái này.');
        }
    }

    private function farmerProfile(User $user): Farmer
    {
        $farmer = $user->farmer;

        if (! $farmer) {
            throw new HttpResponseException(response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ nông dân.',
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
