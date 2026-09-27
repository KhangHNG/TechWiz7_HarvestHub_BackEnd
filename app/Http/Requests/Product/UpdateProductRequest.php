<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFactory;

class UpdateProductRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'farmer_id' => ['sometimes', 'required', $this->livingExists('farmers')],
            'category_id' => ['sometimes', 'required', $this->livingExists('categories')],
            'stock_qty' => ['sometimes', 'required', 'integer', 'min:0'],
            'image_url' => ['sometimes', 'array', 'min:1', 'max:10'],
            'image_url.*' => ['required'],
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->exists('image_url') || $validator->errors()->has('image_url')) {
                return;
            }

            foreach (Arr::wrap($this->all()['image_url'] ?? []) as $index => $image) {
                if (is_string($image)) {
                    if (filter_var($image, FILTER_VALIDATE_URL) === false || strlen($image) > 500) {
                        $validator->errors()->add("image_url.$index", 'Product image must be a valid URL.');
                    }

                    continue;
                }

                if (! $image instanceof UploadedFile) {
                    $validator->errors()->add("image_url.$index", 'Invalid product image.');

                    continue;
                }

                $fileValidator = ValidatorFactory::make(
                    ['file' => $image],
                    ['file' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048']],
                );

                if ($fileValidator->fails()) {
                    $validator->errors()->add("image_url.$index", $fileValidator->errors()->first('file'));
                }
            }
        });
    }
}
