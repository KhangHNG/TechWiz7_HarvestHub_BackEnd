<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'farmer.market'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products->map(fn (Product $product) => $this->transform($product))->values(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::query()
            ->with(['category', 'farmer.market'])
            ->find($id);

        if ($product === null) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->transform($product),
        ]);
    }

    private function transform(Product $product): array
    {
        $farmer = $product->farmer;
        $market = $farmer?->market;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'stock_qty' => $product->stock_qty,
            'image_url' => $product->image_url,
            'created_at' => $product->created_at?->toIso8601String(),
            'category' => $product->category === null ? null : [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ],
            'farmer' => $farmer === null ? null : [
                'id' => $farmer->id,
                'business_name' => $farmer->business_name,
                'rating' => $farmer->rating === null ? null : (float) $farmer->rating,
                'market' => $market === null ? null : [
                    'id' => $market->id,
                    'name' => $market->name,
                    'address' => $market->address,
                ],
            ],
        ];
    }
}
