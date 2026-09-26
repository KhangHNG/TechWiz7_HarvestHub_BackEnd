<?php

namespace App\Http\Requests\Wishlist;

use App\Http\Requests\ApiFormRequest;
use App\Models\Wishlist;
use Illuminate\Contracts\Validation\Validator;

class UpdateWishlistRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', $this->livingCustomer()],
            'product_id' => ['sometimes', 'required', $this->livingExists('products')],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'Khách hàng',
            'product_id' => 'Sản phẩm',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'customer_id.exists' => 'Khách hàng không tồn tại hoặc không có vai trò CUSTOMER.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $wishlist = Wishlist::query()->find($this->route('id'));
            $customerId = $this->input('customer_id', $wishlist?->customer_id);
            $productId = $this->input('product_id', $wishlist?->product_id);

            if (! $customerId || ! $productId) {
                return;
            }

            $exists = Wishlist::query()
                ->where('customer_id', $customerId)
                ->where('product_id', $productId)
                ->whereNull('deleted_at')
                ->when($wishlist, fn ($query) => $query->where('id', '!=', $wishlist->id))
                ->exists();

            if ($exists) {
                $validator->errors()->add('product_id', 'Sản phẩm đã có trong danh sách yêu thích.');
            }
        });
    }
}
