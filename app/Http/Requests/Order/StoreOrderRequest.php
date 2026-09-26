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
            'message' => 'Chỉ khách hàng mới được tạo đơn hàng.',
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
            'status.in' => 'Đơn mới chỉ được tạo ở trạng thái CART.',
            'items.required' => 'Đơn hàng cần ít nhất một sản phẩm.',
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

            $this->validateOrderLines($validator, null, false);
        });
    }
}
