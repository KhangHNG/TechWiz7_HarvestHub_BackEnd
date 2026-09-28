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
            'price' => ['required', 'numeric', 'min:0.01'],
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
            'name' => 'Product name',
            'description' => 'Description',
            'price' => 'Price',
            'farmer_id' => 'Farmer',
            'category_id' => 'Category',
            'stock_qty' => 'Stock quantity',
            'image_url' => 'Product image',
            'image_url.*' => 'Product image',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'farmer_id.exists' => 'The farmer does not exist.',
            'category_id.exists' => 'The category does not exist.',
            'image_url.min' => 'The product needs at least one image.',
            'image_url.max' => 'The product may have at most :max images.',
        ]);
    }
}