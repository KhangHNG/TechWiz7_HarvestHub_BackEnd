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
                        'description' => 'List HarvestHub products in the catalog. Use for questions about which products exist, produce categories (e.g. fruits, vegetables), nutrition or vitamins (e.g. vitamin A, vitamin C, potassium), or products of a named farmer. Keyword filters by product name, category, or nutrient. Optional farmer filters by farm name.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'keyword' => [
                                    'type' => 'STRING',
                                    'description' => 'Optional product name, category (e.g. fruits, vegetables), or nutrient (e.g. vitamin A, vitamin C, potassium). Omit to list the catalog.',
                                ],
                                'farmer' => [
                                    'type' => 'STRING',
                                    'description' => 'Optional English or Vietnamese farm or farmer name (e.g. Han River Produce, Green Leaf Farm). Omit when not specified.',
                                ],
                            ],
                        ],
                    ],
                    [
                        'name' => 'get_product_stock',
                        'description' => 'Look up HarvestHub product price and stock by catalog name and, if given, farm name.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'product' => [
                                    'type' => 'STRING',
                                    'description' => 'English catalog name, for example cherry tomato, water spinach, mango, carrot, tomato.',
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
                                    'description' => 'English or Vietnamese farm or farmer name to look up pickup hours.',
                                ],
                            ],
                            'required' => ['farmer'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Tools declaration compatible with OpenAI / DeepSeek format.
     *
     * @return array<int, array{type: string, function: array{name: string, description: string, parameters: array<string, mixed>}}>
     */
    public function getOpenAiToolsDeclaration(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'list_products',
                    'description' => 'List HarvestHub products in the catalog. Use for questions about which products exist, produce categories (e.g. fruits, vegetables), nutrition or vitamins (e.g. vitamin A, vitamin C, potassium), or products of a named farmer. Keyword filters by product name, category, or nutrient. Optional farmer filters by farm name.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' => 'Optional product name, category (e.g. fruits, vegetables), or nutrient (e.g. vitamin A, vitamin C, potassium). Omit to list the catalog.',
                            ],
                            'farmer' => [
                                'type' => 'string',
                                'description' => 'Optional English or Vietnamese farm or farmer name (e.g. Han River Produce, Green Leaf Farm). Omit when not specified.',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_product_stock',
                    'description' => 'Look up HarvestHub product price and stock by catalog name and, if given, farm name.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'product' => [
                                'type' => 'string',
                                'description' => 'English catalog name, for example cherry tomato, water spinach, mango, carrot, tomato.',
                            ],
                            'farmer' => [
                                'type' => 'string',
                                'description' => 'Farm or farmer name, only when the user mentions one.',
                            ],
                        ],
                        'required' => ['product'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_pickup_slots',
                    'description' => 'Look up the market, address, and opening hours for picking up goods from a farm.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'farmer' => [
                                'type' => 'string',
                                'description' => 'English or Vietnamese farm or farmer name to look up pickup hours.',
                            ],
                        ],
                        'required' => ['farmer'],
                    ],
                ],
            ],
        ];
    }

    public function execute(string $functionName, array $args): array
    {
        Log::info("AI function: {$functionName}", $args);

        return match ($functionName) {
            'list_products' => $this->listProducts($args['keyword'] ?? null, $args['farmer'] ?? null),
            'get_product_stock' => $this->getProductStock(
                (string) ($args['product'] ?? ''),
                $args['farmer'] ?? null,
            ),
            'get_pickup_slots' => $this->getPickupSlots((string) ($args['farmer'] ?? '')),
            default => ['error' => 'Function not found'],
        };
    }

    private function listProducts(mixed $keyword, mixed $farmerName = null): array
    {
        $query = Product::query()->with(['farmer', 'category'])->orderBy('name');

        if (is_string($keyword) && $this->filled($keyword)) {
            $kw = strtolower(trim($keyword));

            // Map Vietnamese category/produce keywords if any
            $kw = match (true) {
                str_contains($kw, 'trái cây') || str_contains($kw, 'hoa quả') => 'fruit',
                str_contains($kw, 'rau củ') || str_contains($kw, 'rau') => 'vegetable',
                str_contains($kw, 'nấm') => 'mushroom',
                str_contains($kw, 'gia vị') => 'spice',
                str_contains($kw, 'thảo mộc') => 'herb',
                str_contains($kw, 'sữa') => 'dairy',
                str_contains($kw, 'mật ong') => 'honey',
                str_contains($kw, 'ngũ cốc') || str_contains($kw, 'gạo') => 'grain',
                str_contains($kw, 'đậu') => 'legume',
                default => $kw,
            };

            // Check for nutrient mappings
            $nutrientItems = match (true) {
                str_contains($kw, 'vitamin a') || str_contains($kw, 'vitamin-a') => [
                    'Carrot', 'Tomato', 'Cherry tomato', 'Pumpkin', 'Hoa Loc mango', 'Organic carrot', 'Organic spinach', 'Water spinach', 'Papaya', 'Watermelon',
                ],
                str_contains($kw, 'vitamin c') || str_contains($kw, 'vitamin-c') => [
                    'Guava', 'King orange', 'Green pomelo', 'Cherry tomato', 'Tomato', 'Cabbage', 'Cauliflower', "Bird's eye chili",
                ],
                str_contains($kw, 'potassium') || str_contains($kw, 'kali') => [
                    'Ripe banana', 'Watermelon', 'Potato', 'Organic spinach',
                ],
                str_contains($kw, 'fiber') || str_contains($kw, 'fibre') || str_contains($kw, 'chất xơ') => [
                    'Carrot', 'Pumpkin', 'Mung beans', 'Red beans', 'Oyster mushroom',
                ],
                default => null,
            };

            if ($nutrientItems !== null) {
                $query->whereIn('name', $nutrientItems);
            } else {
                $rawTerms = preg_split('/[,;\/]+/', $kw);
                $terms = array_filter(array_map('trim', $rawTerms ?: []));

                $query->where(function (Builder $inner) use ($terms, $kw) {
                    if (count($terms) > 1) {
                        foreach ($terms as $term) {
                            $termLike = $this->like($term);
                            $stemLike = $this->like($this->stemWord($term));
                            $inner->orWhere('name', 'like', $termLike)
                                ->orWhere('name', 'like', $stemLike)
                                ->orWhere('description', 'like', $termLike)
                                ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $termLike)->orWhere('name', 'like', $stemLike));
                        }
                    } else {
                        $like = $this->like($kw);
                        $stemLike = $this->like($this->stemWord($kw));
                        $inner->where('name', 'like', $like)
                            ->orWhere('name', 'like', $stemLike)
                            ->orWhere('description', 'like', $like)
                            ->orWhereHas('category', fn (Builder $c) => $c->where('name', 'like', $like)->orWhere('name', 'like', $stemLike));
                    }
                });
            }
        }

        if (is_string($farmerName) && $this->filled($farmerName)) {
            $cleanedFarmer = $this->cleanFarmerName($farmerName);
            $farmerLike = $this->like($cleanedFarmer);
            $query->whereHas('farmer', function (Builder $farmer) use ($farmerLike) {
                $farmer->where('business_name', 'like', $farmerLike);
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
                    'price' => '$'.number_format((float) $product->price, 2),
                    'stock_qty' => $product->stock_qty,
                    'in_stock' => $product->stock_qty > 0,
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

        $stemProduct = $this->stemWord($productName);
        $like = $this->like($stemProduct);
        $rawLike = $this->like($productName);
        $query = Product::query()
            ->with('farmer')
            ->where(function (Builder $inner) use ($like, $rawLike) {
                $inner->where('name', 'like', $rawLike)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('description', 'like', $rawLike);
            });

        if ($this->filled($farmerName)) {
            $cleanedFarmer = $this->cleanFarmerName((string) $farmerName);
            $farmerLike = $this->like($cleanedFarmer);
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
                    'price' => '$'.number_format((float) $product->price, 2),
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

        $cleanedFarmer = $this->cleanFarmerName($farmerName);
        $like = $this->like($cleanedFarmer);
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
                    'city' => $farmer->market->city ?? null,
                    'ward' => $farmer->market->ward ?? null,
                    'operating_hours' => $farmer->market->operating_hours ?? null,
                ];
            })->all(),
        ];
    }

    private function cleanFarmerName(string $name): string
    {
        $cleaned = trim(preg_replace('/\b(nông trại|trang trại|nhà vườn|vườn|farm)\b/ui', '', $name));

        return $cleaned !== '' ? $cleaned : trim($name);
    }

    private function stemWord(string $word): string
    {
        $w = strtolower(trim($word));
        if (str_ends_with($w, 'tomatoes')) {
            return 'tomato';
        }
        if (str_ends_with($w, 'potatoes')) {
            return 'potato';
        }
        if (str_ends_with($w, 'berries')) {
            return substr($w, 0, -3).'y';
        }

        return rtrim($w, 's');
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