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
            'user_id' => 'User',
            'market_id' => 'Market',
            'business_name' => 'Business name',
            'description' => 'Description',
            'rating' => 'Rating',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'user_id.unique' => 'This user already has a farmer profile.',
            'user_id.exists' => 'The user does not exist.',
            'market_id.exists' => 'The market does not exist.',
        ]);
    }
}