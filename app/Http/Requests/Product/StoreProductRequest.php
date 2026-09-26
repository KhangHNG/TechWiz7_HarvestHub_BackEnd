<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiFormRequest;

class StoreProductRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:1'],
            'farmer_id' => ['required', $this->livingExists('farmers')],
            'category_id' => ['required', $this->livingExists('categories')],
            'stock_qty' => ['required', 'integer', 'min:0'],
            'image_url' => ['required', 'array', 'min:1', 'max:10'],
            'image_url.*' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên sản phẩm',
            'description' => 'Mô tả',
            'price' => 'Giá',
            'farmer_id' => 'Nông dân',
            'category_id' => 'Danh mục',
            'stock_qty' => 'Số lượng tồn',
            'image_url' => 'Ảnh sản phẩm',
            'image_url.*' => 'Ảnh sản phẩm',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'farmer_id.exists' => 'Nông dân không tồn tại.',
            'category_id.exists' => 'Danh mục không tồn tại.',
            'image_url.min' => 'Sản phẩm cần ít nhất một ảnh.',
            'image_url.max' => 'Sản phẩm chỉ được tối đa :max ảnh.',
        ]);
    }
}