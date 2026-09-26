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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->exists('image_url') || $validator->errors()->has('image_url')) {
                return;
            }

            foreach (Arr::wrap($this->all()['image_url'] ?? []) as $index => $image) {
                if (is_string($image)) {
                    if (filter_var($image, FILTER_VALIDATE_URL) === false || strlen($image) > 500) {
                        $validator->errors()->add("image_url.$index", 'Ảnh sản phẩm phải là URL hợp lệ.');
                    }

                    continue;
                }

                if (! $image instanceof UploadedFile) {
                    $validator->errors()->add("image_url.$index", 'Ảnh sản phẩm không hợp lệ.');

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
