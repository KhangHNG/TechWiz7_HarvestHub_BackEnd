<?php

namespace App\Http\Requests\Market;

use App\Http\Requests\ApiFormRequest;
use App\Support\LocationValidator;
use Illuminate\Contracts\Validation\Validator;

class StoreMarketRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'ward' => ['required', 'string', 'max:255'],
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

            LocationValidator::assert(
                $validator,
                $this->input('city'),
                $this->input('district'),
                $this->input('ward'),
            );
        });
    }
}
