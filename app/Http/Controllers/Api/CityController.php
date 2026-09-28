<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\WardResource;
use App\Models\City;
use App\Models\District;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;

class CityController extends Controller
{
    public function __construct(protected CityService $cityService) {}

    /**
     * GET /api/cities
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cities retrieved successfully.',
            'data' => CityResource::collection($this->cityService->getCities()),
        ]);
    }

    /**
     * GET /api/cities/{id}/districts
     */
    public function districts(int $id): JsonResponse
    {
        $city = City::query()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Districts retrieved successfully.',
            'data' => DistrictResource::collection($this->cityService->getDistrictsByCity($city->id)),
        ]);
    }

    /**
     * GET /api/districts/{id}/wards
     */
    public function wards(int $id): JsonResponse
    {
        $district = District::query()->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Wards retrieved successfully.',
            'data' => WardResource::collection($this->cityService->getWardsByDistrict($district->id)),
        ]);
    }
}
