<?php

namespace App\Services;

use App\Models\Market;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarketService
{
    public function getMarkets(Request $request)
    {
        $query = Market::query();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('address', 'LIKE', "%{$keyword}%")
                    ->orWhere('city', 'LIKE', "%{$keyword}%")
                    ->orWhere('ward', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->boolean('all')) {
            return $query->orderBy('name', 'asc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
    }

    public function createMarket(array $data)
    {
        return DB::transaction(fn () => Market::create($data));
    }

    public function updateMarket(Market $market, array $data)
    {
        return DB::transaction(function () use ($market, $data) {
            $market->update($data);

            return $market->fresh();
        });
    }

    public function deleteMarket(Market $market)
    {
        return DB::transaction(function () use ($market) {
            if ($market->farmers()->count() > 0) {
                throw new \Exception('Cannot delete a market that still has farmers.');
            }

            return $market->delete();
        });
    }
}
