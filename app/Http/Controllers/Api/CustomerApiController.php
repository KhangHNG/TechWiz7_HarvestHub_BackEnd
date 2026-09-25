<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmerFollow;
use App\Models\Order;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * API cho khách hàng đang đăng nhập (middleware role:CUSTOMER).
 */
class CustomerApiController extends Controller
{
    /**
     * GET /customer/profile — thông tin tài khoản kèm số liệu tổng.
     */
    public function profile(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'orders_count' => Order::query()
                    ->where('customer_id', $user->id)
                    ->where('status', '!=', 'CART')
                    ->count(),
                'wishlist_count' => Wishlist::query()->where('customer_id', $user->id)->count(),
                'following_count' => FarmerFollow::query()->where('customer_id', $user->id)->count(),
            ],
        ]);
    }

    /**
     * GET /customer/orders — lịch sử đơn hàng (không tính giỏ hàng CART).
     */
    public function orders(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $orders = Order::query()
            ->with(['farmer:id,business_name', 'items.product:id,name'])
            ->where('customer_id', $user->id)
            ->where('status', '!=', 'CART')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    /**
     * GET /customer/cart — giỏ hàng là đơn có status CART.
     */
    public function cart(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $cart = Order::query()
            ->with(['items.product.farmer:id,business_name'])
            ->where('customer_id', $user->id)
            ->where('status', 'CART')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart?->id,
                'items' => $cart?->items ?? [],
            ],
        ]);
    }

    /**
     * GET /customer/wishlist
     */
    public function wishlist(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $items = Wishlist::query()
            ->with(['product.farmer:id,business_name', 'product.category:id,name'])
            ->where('customer_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * GET /customer/following — nông dân khách đang theo dõi.
     */
    public function following(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $follows = FarmerFollow::query()
            ->with(['farmer.market:id,name,address'])
            ->where('customer_id', $user->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $follows,
        ]);
    }
}
