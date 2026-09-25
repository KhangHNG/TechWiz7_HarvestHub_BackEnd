<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WishlistResource;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * GET /api/wishlists
     */
    public function index(Request $request): JsonResponse
    {
        $wishlists = $this->wishlistService->getWishlists($request);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả yêu thích thành công.',
                'data' => WishlistResource::collection($wishlists),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách yêu thích thành công.',
            'data' => WishlistResource::collection($wishlists),
            'meta' => [
                'current_page' => $wishlists->currentPage(),
                'last_page' => $wishlists->lastPage(),
                'per_page' => $wishlists->perPage(),
                'total' => $wishlists->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/wishlists
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $wishlist = $this->wishlistService->createWishlist($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Thêm yêu thích thành công.',
                'data' => new WishlistResource($wishlist),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Thêm yêu thích thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/wishlists/{id}
     */
    public function findById($id): JsonResponse
    {
        $wishlist = Wishlist::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Lấy yêu thích thành công.',
            'data' => new WishlistResource($wishlist),
        ], 200);
    }

    /**
     * PUT /api/wishlists/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $wishlist = Wishlist::findOrFail($id);

        $validatedData = $request->validate([
            'customer_id' => 'sometimes|required|exists:users,id',
            'product_id' => 'sometimes|required|exists:products,id',
        ]);

        try {
            $wishlist = $this->wishlistService->updateWishlist($wishlist, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật yêu thích thành công.',
                'data' => new WishlistResource($wishlist),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật yêu thích thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/wishlists/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $wishlist = Wishlist::findOrFail($id);
            $this->wishlistService->deleteWishlist($wishlist);

            return response()->json([
                'success' => true,
                'message' => 'Xóa yêu thích thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa yêu thích thất bại: ' . $e->getMessage(),
            ], 400);
        }
    }
}
