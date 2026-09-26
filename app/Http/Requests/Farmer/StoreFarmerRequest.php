<?php

namespace App\Http\Requests\Farmer;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreFarmerRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                $this->livingExists('users'),
                Rule::unique('farmers', 'user_id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'market_id' => ['required', $this->livingExists('markets')],
            'business_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'rating' => ['sometimes', 'numeric', 'min:0', 'max:5'],
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'Người dùng',
            'market_id' => 'Chợ',
            'business_name' => 'Tên cửa hàng',
            'description' => 'Mô tả',
            'rating' => 'Đánh giá',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'user_id.unique' => 'Người dùng này đã có hồ sơ nông dân.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'market_id.exists' => 'Chợ không tồn tại.',
        ]);
    }
}