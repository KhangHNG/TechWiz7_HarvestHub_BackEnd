<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FarmerFollow\StoreFarmerFollowRequest;
use App\Http\Requests\FarmerFollow\UpdateFarmerFollowRequest;
use App\Http\Resources\FarmerFollowResource;
use App\Models\FarmerFollow;
use App\Services\FarmerFollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmerFollowController extends Controller
{
    protected $farmerFollowService;

    public function __construct(FarmerFollowService $farmerFollowService)
    {
        $this->farmerFollowService = $farmerFollowService;
    }

    /**
     * GET /api/follows
     */
    public function index(Request $request): JsonResponse
    {
        $follows = $this->farmerFollowService->getFollows($request);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả lượt theo dõi thành công.',
                'data' => FarmerFollowResource::collection($follows),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách theo dõi thành công.',
            'data' => FarmerFollowResource::collection($follows),
            'meta' => [
                'current_page' => $follows->currentPage(),
                'last_page' => $follows->lastPage(),
                'per_page' => $follows->perPage(),
                'total' => $follows->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/follows
     */
    public function store(StoreFarmerFollowRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $follow = $this->farmerFollowService->createFollow($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Theo dõi nông dân thành công.',
                'data' => new FarmerFollowResource($follow),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Theo dõi nông dân thất bại: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/follows/{id}
     */
    public function findById($id): JsonResponse
    {
        $follow = FarmerFollow::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Lấy lượt theo dõi thành công.',
            'data' => new FarmerFollowResource($follow),
        ], 200);
    }

    /**
     * PUT /api/follows/{id}
     */
    public function update(UpdateFarmerFollowRequest $request, $id): JsonResponse
    {
        $follow = FarmerFollow::findOrFail($id);

        $validatedData = $request->validated();

        try {
            $follow = $this->farmerFollowService->updateFollow($follow, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật theo dõi thành công.',
                'data' => new FarmerFollowResource($follow),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật theo dõi thất bại: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/follows/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $follow = FarmerFollow::findOrFail($id);
            $this->farmerFollowService->deleteFollow($follow);

            return response()->json([
                'success' => true,
                'message' => 'Bỏ theo dõi thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bỏ theo dõi thất bại: '.$e->getMessage(),
            ], 400);
        }
    }
}
