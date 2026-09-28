<?php

namespace App\Support;

use App\Models\City;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Contracts\Validation\Validator;

class LocationValidator
{
    public static function assert(Validator $validator, mixed $city, mixed $district, mixed $ward): void
    {
        if (blank($city) && blank($district) && blank($ward)) {
            return;
        }

        $cityModel = City::query()->where('name', $city)->first();

        if (! $cityModel) {
            $validator->errors()->add('city', 'Thành phố không hợp lệ.');

            return;
        }

        $districtModel = District::query()
            ->where('city_id', $cityModel->id)
            ->where('name', $district)
            ->first();

        if (! $districtModel) {
            $validator->errors()->add('district', 'Quận không thuộc thành phố đã chọn.');

            return;
        }

        $wardExists = Ward::query()
            ->where('district_id', $districtModel->id)
            ->where('name', $ward)
            ->exists();

        if (! $wardExists) {
            $validator->errors()->add('ward', 'Phường, xã không thuộc quận đã chọn.');
        }
    }
}
