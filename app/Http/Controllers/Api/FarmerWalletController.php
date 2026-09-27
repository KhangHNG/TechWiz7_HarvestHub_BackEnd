<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FarmerWalletTransaction;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class FarmerWalletController extends Controller
{
    public function index(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();
        $farmer = $user?->farmer;

        if (! $farmer) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hồ sơ nông dân.',
            ], 404);
        }

        $totals = FarmerWalletTransaction::query()
            ->where('farmer_id', $farmer->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as credit_total")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as debit_total")
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Lấy số dư ví thành công.',
            'data' => [
                'balance' => (float) $totals->credit_total - (float) $totals->debit_total,
            ],
        ]);
    }
}
