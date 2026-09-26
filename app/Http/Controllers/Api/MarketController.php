<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Market\StoreMarketRequest;
use App\Http\Requests\Market\UpdateMarketRequest;
use App\Http\Resources\MarketResource;
use App\Models\Market;
use App\Services\MarketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    protected $marketService;

    public function __construct(MarketService $marketService)
    {
        $this->marketService = $marketService;
    }

    /**
     * GET /api/markets
     */
    public function index(Request $request): JsonResponse
    {
        $markets = $this->marketService->getMarkets($request);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả chợ thành công.',
                'data' => MarketResource::collection($markets),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách chợ thành công.',
            'data' => MarketResource::collection($markets),
            'meta' => [
                'current_page' => $markets->currentPage(),
                'last_page' => $markets->lastPage(),
                'per_page' => $markets->perPage(),
                'total' => $markets->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/markets
     */
    public function store(StoreMarketRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $market = $this->marketService->createMarket($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Tạo chợ thành công.',
                'data' => new MarketResource($market),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo chợ thất bại: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/markets/{id}
     */
    public function findById($id): JsonResponse
    {
        $market = Market::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Lấy chợ thành công.',
            'data' => new MarketResource($market),
        ], 200);
    }

    /**
     * PUT /api/markets/{id}
     */
    public function update(UpdateMarketRequest $request, $id): JsonResponse
    {
        $market = Market::findOrFail($id);

        $validatedData = $request->validated();

        try {
            $market = $this->marketService->updateMarket($market, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chợ thành công.',
                'data' => new MarketResource($market),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật chợ thất bại: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/markets/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $market = Market::findOrFail($id);
            $this->marketService->deleteMarket($market);

            return response()->json([
                'success' => true,
                'message' => 'Xóa chợ thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa chợ thất bại: '.$e->getMessage(),
            ], 400);
        }
    }
}
