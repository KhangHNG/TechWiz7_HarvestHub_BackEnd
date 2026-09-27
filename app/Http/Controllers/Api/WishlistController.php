<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wishlist\StoreWishlistRequest;
use App\Http\Requests\Wishlist\UpdateWishlistRequest;
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
                'message' => 'All wishlists retrieved successfully.',
                'data' => WishlistResource::collection($wishlists),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Wishlists retrieved successfully.',
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
    public function store(StoreWishlistRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            $wishlist = $this->wishlistService->createWishlist($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Added to wishlist successfully.',
                'data' => new WishlistResource($wishlist),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to wishlist: '.$e->getMessage(),
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
            'message' => 'Wishlist item retrieved successfully.',
            'data' => new WishlistResource($wishlist),
        ], 200);
    }

    /**
     * PUT /api/wishlists/{id}
     */
    public function update(UpdateWishlistRequest $request, $id): JsonResponse
    {
        $wishlist = Wishlist::findOrFail($id);

        $validatedData = $request->validated();

        try {
            $wishlist = $this->wishlistService->updateWishlist($wishlist, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Wishlist updated successfully.',
                'data' => new WishlistResource($wishlist),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wishlist: '.$e->getMessage(),
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
                'message' => 'Removed from wishlist successfully.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from wishlist: '.$e->getMessage(),
            ], 400);
        }
    }
}
