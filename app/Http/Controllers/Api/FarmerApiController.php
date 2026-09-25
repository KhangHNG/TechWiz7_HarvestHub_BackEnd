<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * API cho nông dân đang đăng nhập (middleware role:FARMER).
 */
class FarmerApiController extends Controller
{
    /**
     * GET /farmer/profile
     */
    public function profile(): JsonResponse
    {
        $farmer = $this->currentFarmer();
        if (! $farmer) {
            return $this->notFarmer();
        }

        $farmer->load(['user:id,full_name,email,phone,address', 'market']);
        $farmer->loadCount('products');

        return response()->json([
            'success' => true,
            'data' => $farmer,
        ]);
    }

    /**
     * GET /farmer/products — sản phẩm của nông dân đang đăng nhập.
     */
    public function products(Request $request): JsonResponse
    {
        $farmer = $this->currentFarmer();
        if (! $farmer) {
            return $this->notFarmer();
        }

        $query = Product::query()
            ->with('category:id,name')
            ->where('farmer_id', $farmer->id);

        if ($request->filled('keyword')) {
            $query->where('name', 'LIKE', "%{$request->keyword}%");
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->get(),
        ]);
    }

    /**
     * GET /farmer/orders — đơn hàng gửi tới nông dân (không tính giỏ hàng CART).
     */
    public function orders(Request $request): JsonResponse
    {
        $farmer = $this->currentFarmer();
        if (! $farmer) {
            return $this->notFarmer();
        }

        $query = Order::query()
            ->with(['customer:id,full_name,phone', 'items'])
            ->where('farmer_id', $farmer->id)
            ->where('status', '!=', 'CART');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->get(),
        ]);
    }

    private function currentFarmer(): ?Farmer
    {
        $user = JWTAuth::parseToken()->authenticate();

        return Farmer::query()->where('user_id', $user->id)->first();
    }

    private function notFarmer(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tài khoản chưa có hồ sơ nông dân.',
        ], 404);
    }
}
