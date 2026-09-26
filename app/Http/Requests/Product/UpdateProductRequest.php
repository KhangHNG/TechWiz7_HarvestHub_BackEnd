<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiFormRequest;

class UpdateProductRequest extends ApiFormRequest
{
    public function rules(): array
    {
        $imageRules = $this->hasFile('image_url')
            ? ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048']
            : ['nullable', 'string', 'max:500'];

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'farmer_id' => ['sometimes', 'required', $this->livingExists('farmers')],
            'category_id' => ['sometimes', 'required', $this->livingExists('categories')],
            'stock_qty' => ['sometimes', 'required', 'integer', 'min:0'],
            'image_url' => $imageRules,
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
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'farmer_id.exists' => 'Nông dân không tồn tại.',
            'category_id.exists' => 'Danh mục không tồn tại.',
        ]);
    }
}
