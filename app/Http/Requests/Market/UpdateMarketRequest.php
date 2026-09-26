<?php

namespace App\Http\Requests\Market;

use App\Http\Requests\ApiFormRequest;

class UpdateMarketRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên chợ',
            'address' => 'Địa chỉ',
            'latitude' => 'Vĩ độ',
            'longitude' => 'Kinh độ',
            'operating_hours' => 'Giờ hoạt động',
            'is_active' => 'Trạng thái hoạt động',
        ];
    }
}
