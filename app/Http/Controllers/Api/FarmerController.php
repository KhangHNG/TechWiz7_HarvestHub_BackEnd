<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\StoreFarmerRequest;
use App\Http\Requests\Farmer\UpdateFarmerRequest;
use App\Http\Resources\FarmerResource;
use App\Models\Farmer;
use App\Services\FarmerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmerController extends Controller
{
    protected $farmerService;

    public function __construct(FarmerService $farmerService)
    {
        $this->farmerService = $farmerService;
    }

    /**
     * GET /api/farmers
     */
    public function index(Request $request): JsonResponse
    {
        $farmers = $this->farmerService->getFarmers($request);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả nông dân thành công.',
                'data' => FarmerResource::collection($farmers),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách nông dân thành công.',
            'data' => FarmerResource::collection($farmers),
            'meta' => [
                'current_page' => $farmers->currentPage(),
                'last_page' => $farmers->lastPage(),
                'per_page' => $farmers->perPage(),
                'total' => $farmers->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/farmers
     */
    public function store(StoreFarmerRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $farmer = $this->farmerService->createFarmer($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Tạo nông dân thành công.',
                'data' => new FarmerResource($farmer),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo nông dân thất bại: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/farmers/{id}
     */
    public function findById($id): JsonResponse
    {
        $farmer = Farmer::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Lấy nông dân thành công.',
            'data' => new FarmerResource($farmer),
        ], 200);
    }

    /**
     * PUT /api/farmers/{id}
     */
    public function update(UpdateFarmerRequest $request, $id): JsonResponse
    {
        $farmer = Farmer::findOrFail($id);

        $validatedData = $request->validated();

        try {
            $farmer = $this->farmerService->updateFarmer($farmer, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật nông dân thành công.',
                'data' => new FarmerResource($farmer),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật nông dân thất bại: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/farmers/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $farmer = Farmer::findOrFail($id);
            $this->farmerService->deleteFarmer($farmer);

            return response()->json([
                'success' => true,
                'message' => 'Xóa nông dân thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa nông dân thất bại: '.$e->getMessage(),
            ], 400);
        }
    }
}
