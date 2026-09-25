<?php

namespace App\Services;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistService
{
    public function getWishlists(Request $request)
    {
        $query = Wishlist::query();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->boolean('all')) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 10));
    }

    public function createWishlist(array $data)
    {
        return DB::transaction(fn () => Wishlist::create($data));
    }

    public function updateWishlist(Wishlist $wishlist, array $data)
    {
        return DB::transaction(function () use ($wishlist, $data) {
            $wishlist->update($data);

            return $wishlist->fresh();
        });
    }

    public function deleteWishlist(Wishlist $wishlist)
    {
        return DB::transaction(fn () => $wishlist->delete());
    }
}
