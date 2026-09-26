<?php

namespace App\Services\Ai;

use App\Models\Farmer;
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
                        'name' => 'list_products',
                        'description' => 'List HarvestHub products in the catalog. Use for questions about which products exist. Optional keyword filters by product name or description.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'keyword' => [
                                    'type' => 'STRING',
                                    'description' => 'Optional Vietnamese product keyword. Omit it to list the catalog.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'get_product_stock',
                        'description' => 'Look up HarvestHub product price and stock by Vietnamese catalog name and, if given, farm name.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'product' => [
                                    'type' => 'STRING',
                                    'description' => 'Vietnamese catalog name, for example cà chua bi, rau muống, xoài. Do not pass an English name.',
                                ],
                                'farmer' => [
                                    'type' => 'STRING',
                                    'description' => 'Farm or farmer name, only when the user mentions one.',
                                ],
                            ],
                            'required' => ['product'],
                        ],
                    ],
                    [
                        'name' => 'get_pickup_slots',
                        'description' => 'Look up the market, address, and opening hours for picking up goods from a farm.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'farmer' => [
                                    'type' => 'STRING',
                                    'description' => 'Vietnamese farm or farmer name to look up pickup hours.',
                                ],
                            ],
                            'required' => ['farmer'],
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
            'list_products' => $this->listProducts($args['keyword'] ?? null),
            'get_product_stock' => $this->getProductStock(
                (string) ($args['product'] ?? ''),
                $args['farmer'] ?? null,
            ),
            'get_pickup_slots' => $this->getPickupSlots((string) ($args['farmer'] ?? '')),
            default => ['error' => 'Function not found'],
        };
    }

    private function listProducts(mixed $keyword): array
    {
        $query = Product::query()->with(['farmer', 'category'])->orderBy('name');

        if (is_string($keyword) && $this->filled($keyword)) {
            $like = $this->like($keyword);
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        $products = $query->limit(20)->get();

        if ($products->isEmpty()) {
            return [
                'status' => 'not_found',
                'message' => 'not_found',
            ];
        }

        return [
            'status' => 'success',
            'data' => $products->map(function (Product $product) {
                return [
                    'product_name' => $product->name,
                    'category_name' => $product->category->name ?? null,
                    'farmer_name' => $product->farmer->business_name ?? null,
                    'price' => number_format((float) $product->price).' VND',
                    'stock_qty' => $product->stock_qty,
                ];
            })->all(),
        ];
    }

    private function getProductStock(string $productName, ?string $farmerName): array
    {
        if (! $this->filled($productName)) {
            return [
                'status' => 'error',
                'message' => 'missing_product',
            ];
        }

        $like = $this->like($productName);
        $query = Product::query()
            ->with('farmer')
            ->where(function (Builder $inner) use ($like) {
                $inner->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });

        if ($this->filled($farmerName)) {
            $farmerLike = $this->like($farmerName);
            $query->whereHas('farmer', function (Builder $farmer) use ($farmerLike) {
                $farmer->where('business_name', 'like', $farmerLike);
            });
        }

        $products = $query->orderBy('name')->limit(8)->get();

        if ($products->isEmpty()) {
            return [
                'status' => 'not_found',
                'message' => 'not_found',
            ];
        }

        return [
            'status' => 'success',
            'data' => $products->map(function (Product $product) {
                return [
                    'product_name' => $product->name,
                    'farmer_name' => $product->farmer->business_name ?? null,
                    'price' => number_format((float) $product->price).' VND',
                    'stock_qty' => $product->stock_qty,
                    'in_stock' => $product->stock_qty > 0,
                ];
            })->all(),
        ];
    }

    private function getPickupSlots(string $farmerName): array
    {
        if (! $this->filled($farmerName)) {
            return [
                'status' => 'error',
                'message' => 'missing_farmer',
            ];
        }

        $like = $this->like($farmerName);
        $farmers = Farmer::query()
            ->with('market')
            ->where('business_name', 'like', $like)
            ->orderBy('business_name')
            ->limit(5)
            ->get();

        if ($farmers->isEmpty()) {
            return [
                'status' => 'not_found',
                'message' => 'not_found',
            ];
        }

        return [
            'status' => 'success',
            'data' => $farmers->map(function (Farmer $farmer) {
                return [
                    'farmer_name' => $farmer->business_name,
                    'market_name' => $farmer->market->name ?? null,
                    'address' => $farmer->market->address ?? null,
                    'operating_hours' => $farmer->market->operating_hours ?? null,
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