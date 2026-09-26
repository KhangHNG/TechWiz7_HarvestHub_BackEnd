<?php

namespace App\Http\Requests\FarmerFollow;

use App\Http\Requests\ApiFormRequest;
use App\Models\FarmerFollow;
use Illuminate\Contracts\Validation\Validator;

class UpdateFarmerFollowRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', $this->livingCustomer()],
            'farmer_id' => ['sometimes', 'required', $this->livingExists('farmers')],
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

            $follow = FarmerFollow::query()->find($this->route('id'));
            $customerId = $this->input('customer_id', $follow?->customer_id);
            $farmerId = $this->input('farmer_id', $follow?->farmer_id);

            if (! $customerId || ! $farmerId) {
                return;
            }

            $exists = FarmerFollow::query()
                ->where('customer_id', $customerId)
                ->where('farmer_id', $farmerId)
                ->whereNull('deleted_at')
                ->when($follow, fn ($query) => $query->where('id', '!=', $follow->id))
                ->exists();

            if ($exists) {
                $validator->errors()->add('farmer_id', 'Bạn đã theo dõi nông dân này.');
            }
        });
    }
}
