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
                'message' => 'All follows retrieved successfully.',
                'data' => FarmerFollowResource::collection($follows),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Follows retrieved successfully.',
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
                'message' => 'Farmer followed successfully.',
                'data' => new FarmerFollowResource($follow),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to follow farmer: '.$e->getMessage(),
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
            'message' => 'Follow retrieved successfully.',
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
                'message' => 'Follow updated successfully.',
                'data' => new FarmerFollowResource($follow),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update follow: '.$e->getMessage(),
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
                'message' => 'Unfollowed successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unfollow: '.$e->getMessage(),
            ], 400);
        }
    }
}
