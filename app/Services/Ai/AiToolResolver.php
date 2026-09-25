<?php

namespace App\Services\Ai;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class AiToolResolver
{
    public function getToolsDeclaration(): array
    {
        return [
            [
                'function_declarations' => [
                    [
                        'name' => 'searchProducts',
                        'description' => 'Tra cứu nông sản trong cơ sở dữ liệu HarvestHub: tên, mô tả, giá, tồn kho, danh mục và trang trại. Gọi công cụ này trước khi trả lời mọi câu hỏi về sản phẩm.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'product_name' => [
                                    'type' => 'STRING',
                                    'description' => 'Tên hoặc một phần tên nông sản, ví dụ Cà chua, Rau muống, Xoài. Bỏ trống nếu người dùng hỏi chung.',
                                ],
                                'farmer_name' => [
                                    'type' => 'STRING',
                                    'description' => 'Tên trang trại hoặc nông dân, nếu người dùng nhắc tới.',
                                ],
                                'category_name' => [
                                    'type' => 'STRING',
                                    'description' => 'Danh mục, ví dụ Vegetables, Fruits, nếu người dùng nhắc tới.',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function execute(string $functionName, array $args): array
    {
        Log::info("Gemini function: {$functionName}", $args);

        return match ($functionName) {
            'searchProducts' => $this->searchProducts(
                $args['product_name'] ?? null,
                $args['farmer_name'] ?? null,
                $args['category_name'] ?? null,
            ),
            default => ['error' => 'Function not found'],
        };
    }

    private function searchProducts(?string $productName, ?string $farmerName, ?string $categoryName): array
    {
        $query = Product::query()->with(['farmer', 'category']);

        if ($this->filled($productName)) {
            $like = $this->like($productName);
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        if ($this->filled($farmerName)) {
            $like = $this->like($farmerName);
            $query->whereHas('farmer', function (Builder $farmer) use ($like) {
                $farmer->where('business_name', 'like', $like);
            });
        }

        if ($this->filled($categoryName)) {
            $like = $this->like($categoryName);
            $query->whereHas('category', function (Builder $category) use ($like) {
                $category->where('name', 'like', $like);
            });
        }

        $products = $query->orderBy('name')->limit(8)->get();

        if ($products->isEmpty()) {
            return [
                'status' => 'not_found',
                'message' => 'Không có sản phẩm khớp với yêu cầu.',
            ];
        }

        return [
            'status' => 'success',
            'data' => $products->map(function (Product $product) {
                return [
                    'product_name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category->name ?? null,
                    'farmer_name' => $product->farmer->business_name ?? null,
                    'price' => number_format((float) $product->price).' VNĐ',
                    'stock_qty' => $product->stock_qty,
                    'in_stock' => $product->stock_qty > 0,
                ];
            })->all(),
        ];
    }

    private function filled(?string $value): bool
    {
        return $value !== null && trim($value) !== '';
    }

    private function like(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($value));

        return '%'.$escaped.'%';
    }
}
