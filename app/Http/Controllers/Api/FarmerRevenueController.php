<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\RevenueRequest;
use App\Services\FarmerRevenueService;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class FarmerRevenueController extends Controller
{
    public function __construct(private FarmerRevenueService $revenue) {}

    public function index(RevenueRequest $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $farmer = $user?->farmer;

        if (! $farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ nông dân.',
            ], 404);
        }

        $summary = $this->revenue->summarize(
            $farmer->id,
            (string) $request->input('period'),
            $request->filled('year') ? (int) $request->input('year') : null,
            $request->filled('month') ? (int) $request->input('month') : null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Lấy doanh thu thành công.',
            'data' => $summary,
        ]);
    }
}
