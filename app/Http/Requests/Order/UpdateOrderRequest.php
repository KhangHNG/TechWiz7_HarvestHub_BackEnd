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
            'payment_method' => ['sometimes', 'in:COD,BANK_TRANSFER'],
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
            'delivery_address' => 'Delivery address',
            'status' => 'Status',
            'payment_method' => 'Payment method',
            'total_price' => 'Total',
            'items' => 'Order items',
            'items.*.product_id' => 'Product',
            'items.*.product_name' => 'Product name',
            'items.*.unit_price' => 'Unit price',
            'items.*.quantity' => 'Quantity',
            'items.*.line_total' => 'Line total',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'items.min' => 'The order needs at least one product.',
            'items.*.product_id.exists' => 'The product does not exist.',
            'items.*.product_id.distinct' => 'Duplicate products in the order.',
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
            $this->validatePaymentMethod($validator, $order);
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
                "Cannot change status from {$current} to {$next}.",
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
                'A delivery address is required before changing status.',
            );
        }

        $hasItems = $this->exists('items')
            ? count((array) $this->input('items')) > 0
            : $order->items->isNotEmpty();

        if (! $hasItems) {
            $validator->errors()->add(
                'items',
                'The order needs at least one product before changing status.',
            );
        }
    }

    private function validatePaymentMethod(Validator $validator, Order $order): void
    {
        if (! $this->exists('payment_method')) {
            return;
        }

        $current = (string) $order->status;
        $next = $this->exists('status') ? (string) $this->input('status') : $current;

        if ($current === 'CART' && $next === 'PENDING') {
            return;
        }

        $validator->errors()->add(
            'payment_method',
            'Payment method can only be selected when placing the order.',
        );
    }

    private function validateItemChange(Validator $validator, Order $order): void
    {
        if (! $this->exists('items')) {
            return;
        }

        if ($order->status !== 'CART') {
            $validator->errors()->add('items', 'Only cart orders can have their products edited.');

            return;
        }

        $this->validateOrderLines($validator, null, false);
    }
}
