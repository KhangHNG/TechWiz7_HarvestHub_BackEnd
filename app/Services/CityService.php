<?php

namespace App\Services;

use App\Models\City;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Support\Collection;

class CityService
{
    public function getCities(): Collection
    {
        return City::query()->orderBy('name')->get();
    }

    public function getDistrictsByCity(int $cityId): Collection
    {
        return District::query()
            ->where('city_id', $cityId)
            ->orderBy('name')
            ->get();
    }

    public function getWardsByDistrict(int $districtId): Collection
    {
        return Ward::query()
            ->where('district_id', $districtId)
            ->orderBy('name')
            ->get();
    }
}
