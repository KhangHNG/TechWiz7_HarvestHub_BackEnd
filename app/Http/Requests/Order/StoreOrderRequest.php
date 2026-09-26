<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiFormRequest;
use App\Models\Order;
use Illuminate\Contracts\Validation\Validator;

class StoreOrderRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['required', $this->livingCustomer()],
            'farmer_id' => ['nullable', $this->livingExists('farmers')],
            'delivery_address' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:CART,PENDING,CONFIRMED,READY_FOR_PICKUP,COMPLETED,CANCELLED'],
            'total_price' => ['nullable', 'numeric', 'min:0'],
            'items' => ['nullable', 'array'],
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
            'customer_id' => 'Khách hàng',
            'farmer_id' => 'Nông dân',
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
            'customer_id.exists' => 'Khách hàng không tồn tại hoặc không có vai trò CUSTOMER.',
            'farmer_id.exists' => 'Nông dân không tồn tại.',
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

            $customerId = $this->input('customer_id');
            $status = $this->input('status', 'CART');

            $exists = Order::query()
                ->where('customer_id', $customerId)
                ->where('status', $status)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                $validator->errors()->add('status', 'Khách hàng đã có đơn hàng với trạng thái này.');
            }
        });
    }
}
