<?php

namespace App\Http\Requests\Market;

use App\Http\Requests\ApiFormRequest;
use App\Models\Market;
use App\Support\LocationValidator;
use Illuminate\Contracts\Validation\Validator;

class UpdateMarketRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'address' => ['sometimes', 'required', 'string'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'district' => ['sometimes', 'required', 'string', 'max:255'],
            'ward' => ['sometimes', 'required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Market name',
            'address' => 'Address',
            'city' => 'City',
            'district' => 'District',
            'ward' => 'Ward',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'operating_hours' => 'Operating hours',
            'is_active' => 'Active status',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $market = Market::query()->find($this->route('id'));

            LocationValidator::assert(
                $validator,
                $this->exists('city') ? $this->input('city') : $market?->city,
                $this->exists('district') ? $this->input('district') : $market?->district,
                $this->exists('ward') ? $this->input('ward') : $market?->ward,
            );
        });
    }
}
