<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends ApiFormRequest
{
    use ValidatesOrderLines;

    public function authorize(): bool
    {
        return $this->user()?->role === 'CUSTOMER';
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Only customers can create orders.',
        ], 403));
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('status')) {
            $this->merge(['status' => 'CART']);
        }

        $user = $this->user();
        if ($user && $user->role === 'CUSTOMER') {
            $this->merge(['customer_id' => $user->id]);
        }

        $this->merge(['farmer_id' => null]);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', $this->livingCustomer()],
            'farmer_id' => ['nullable', $this->livingExists('farmers')],
            'delivery_address' => ['nullable', 'string'],
            'status' => ['required', 'in:CART'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
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
            'customer_id' => 'Customer',
            'farmer_id' => 'Farmer',
            'delivery_address' => 'Delivery address',
            'status' => 'Status',
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
            'customer_id.exists' => 'The customer does not exist or does not have the CUSTOMER role.',
            'farmer_id.exists' => 'The farmer does not exist.',
            'status.in' => 'New orders can only be created with CART status.',
            'items.required' => 'The order needs at least one product.',
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

            $this->validateOrderLines($validator, null, false);
        });
    }
}
