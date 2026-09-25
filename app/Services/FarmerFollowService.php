<?php

namespace App\Services;

use App\Models\FarmerFollow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FarmerFollowService
{
    public function getFollows(Request $request)
    {
        $query = FarmerFollow::query();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        if ($request->boolean('all')) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
    }

    public function createFollow(array $data)
    {
        return DB::transaction(fn () => FarmerFollow::create($data));
    }

    public function updateFollow(FarmerFollow $follow, array $data)
    {
        return DB::transaction(function () use ($follow, $data) {
            $follow->update($data);

            return $follow->fresh();
        });
    }

    public function deleteFollow(FarmerFollow $follow)
    {
        return DB::transaction(fn () => $follow->delete());
    }
}
