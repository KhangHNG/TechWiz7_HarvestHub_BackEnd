<?php

namespace App\Http\Requests\FarmerFollow;

use App\Http\Requests\ApiFormRequest;
use App\Models\FarmerFollow;
use Illuminate\Contracts\Validation\Validator;

class StoreFarmerFollowRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['required', $this->livingCustomer()],
            'farmer_id' => ['required', $this->livingExists('farmers')],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_id' => 'Khách hàng',
            'farmer_id' => 'Nông dân',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'customer_id.exists' => 'Khách hàng không tồn tại hoặc không có vai trò CUSTOMER.',
            'farmer_id.exists' => 'Nông dân không tồn tại.',
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $exists = FarmerFollow::query()
                ->where('customer_id', $this->input('customer_id'))
                ->where('farmer_id', $this->input('farmer_id'))
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                $validator->errors()->add('farmer_id', 'Bạn đã theo dõi nông dân này.');
            }
        });
    }
}
