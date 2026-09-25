<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use Illuminate\Http\JsonResponse;

/**
 * API công khai (không cần đăng nhập) cho màn hình khách hàng:
 * danh mục, chợ, nông dân.
 */
class CatalogApiController extends Controller
{
    /**
     * GET /categories
     */
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->select(['id', 'name'])
            ->withCount('products')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * GET /markets — chỉ chợ đang hoạt động.
     */
    public function markets(): JsonResponse
    {
        $markets = Market::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get(['id', 'name', 'address', 'latitude', 'longitude', 'operating_hours']);

        return response()->json([
            'success' => true,
            'data' => $markets,
        ]);
    }

    /**
     * GET /farmers
     */
    public function farmers(): JsonResponse
    {
        $farmers = Farmer::query()
            ->with(['user:id,full_name,phone', 'market:id,name,address,operating_hours'])
            ->withCount('products')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $farmers,
        ]);
    }

    /**
     * GET /farmers/{id} — hồ sơ nông dân kèm danh sách sản phẩm.
     */
    public function farmer(int $id): JsonResponse
    {
        $farmer = Farmer::query()
            ->with([
                'user:id,full_name,phone',
                'market:id,name,address,operating_hours',
                'products.category:id,name',
            ])
            ->find($id);

        if (! $farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy nông dân.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $farmer,
        ]);
    }
}
