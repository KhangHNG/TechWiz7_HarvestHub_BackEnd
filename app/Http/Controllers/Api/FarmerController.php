<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\StoreCoverRequest;
use App\Http\Requests\Farmer\StoreFarmerRequest;
use App\Http\Requests\Farmer\UpdateFarmerRequest;
use App\Http\Resources\FarmerResource;
use App\Models\Farmer;
use App\Models\User;
use App\Services\FarmerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $farmer = Farmer::with('user')->findOrFail($id);

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

        if (array_key_exists('is_accepting_orders', $validatedData)) {
            $denied = $this->denyUnlessFarmOwner($farmer);
            if ($denied) {
                return $denied;
            }
        }

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
     * POST /api/farmers/{id}/cover
     */
    public function updateCover(StoreCoverRequest $request, $id): JsonResponse
    {
        $farmer = Farmer::findOrFail($id);
        $denied = $this->denyUnlessFarmOwner($farmer);
        if ($denied) {
            return $denied;
        }

        try {
            $farmer = $this->farmerService->updateCover($farmer, $request->file('cover'));

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật ảnh bìa thành công.',
                'data' => new FarmerResource($farmer),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật ảnh bìa thất bại: '.$e->getMessage(),
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

    private function denyUnlessFarmOwner(Farmer $farmer): ?JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (TokenExpiredException) {
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn, vui lòng đăng nhập lại.',
            ], 401);
        } catch (TokenInvalidException) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ.',
            ], 401);
        } catch (JWTException) {
            return response()->json([
                'success' => false,
                'message' => 'Token bị thiếu hoặc không thể giải mã.',
            ], 401);
        }

        if (! $user instanceof User || $user->role !== 'FARMER' || (int) $user->id !== (int) $farmer->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện thao tác này.',
            ], 403);
        }

        return null;
    }
}
