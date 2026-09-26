<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiFormRequest;
use App\Models\Order;
use Illuminate\Contracts\Validation\Validator;

class UpdateOrderRequest extends ApiFormRequest
{
    use ValidatesOrderLines;

    /**
     * @var array<string, array<int, string>>
     */
    private const TRANSITIONS = [
        'CART' => ['PENDING', 'CANCELLED'],
        'PENDING' => ['CONFIRMED', 'CANCELLED'],
        'CONFIRMED' => ['READY_FOR_PICKUP', 'CANCELLED'],
        'READY_FOR_PICKUP' => ['COMPLETED', 'CANCELLED'],
        'COMPLETED' => [],
        'CANCELLED' => [],
    ];

    public function rules(): array
    {
        return [
            'delivery_address' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:CART,PENDING,CONFIRMED,READY_FOR_PICKUP,COMPLETED,CANCELLED'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'distinct', $this->livingExists('products')],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'delivery_address' => 'Địa chỉ giao hàng',
            'status' => 'Trạng thái',
            'total_price' => 'Tổng tiền',
            'items' => 'Sản phẩm trong đơn',
            'items.*.product_id' => 'Sản phẩm',
            'items.*.product_name' => 'Tên sản phẩm',
            'items.*.unit_price' => 'Đơn giá',
            'items.*.quantity' => 'Số lượng',
            'items.*.line_total' => 'Thành tiền',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'items.min' => 'Đơn hàng cần ít nhất một sản phẩm.',
            'items.*.product_id.exists' => 'Sản phẩm không tồn tại.',
            'items.*.product_id.distinct' => 'Sản phẩm bị trùng trong đơn hàng.',
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $order = Order::query()->with('items')->find($this->route('id'));
            if (! $order) {
                return;
            }

            $this->validateStatusChange($validator, $order);
            $this->validateItemChange($validator, $order);
        });
    }

    private function validateStatusChange(Validator $validator, Order $order): void
    {
        if (! $this->exists('status')) {
            return;
        }

        $current = (string) $order->status;
        $next = (string) $this->input('status');
        if ($next === $current) {
            return;
        }

        $allowed = self::TRANSITIONS[$current] ?? [];
        if (! in_array($next, $allowed, true)) {
            $validator->errors()->add(
                'status',
                "Không thể chuyển trạng thái từ {$current} sang {$next}.",
            );

            return;
        }

        if ($current !== 'CART') {
            return;
        }

        $address = $this->exists('delivery_address')
            ? $this->input('delivery_address')
            : $order->delivery_address;

        if (! is_string($address) || trim($address) === '') {
            $validator->errors()->add(
                'delivery_address',
                'Cần địa chỉ giao hàng trước khi chuyển trạng thái.',
            );
        }

        $hasItems = $this->exists('items')
            ? count((array) $this->input('items')) > 0
            : $order->items->isNotEmpty();

        if (! $hasItems) {
            $validator->errors()->add(
                'items',
                'Đơn hàng cần ít nhất một sản phẩm trước khi chuyển trạng thái.',
            );
        }
    }

    private function validateItemChange(Validator $validator, Order $order): void
    {
        if (! $this->exists('items')) {
            return;
        }

        if ($order->status !== 'CART') {
            $validator->errors()->add('items', 'Chỉ đơn giỏ hàng mới được sửa sản phẩm.');

            return;
        }

        $this->validateOrderLines($validator, null, false);
    }
}
