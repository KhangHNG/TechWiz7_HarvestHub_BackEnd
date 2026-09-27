<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Farmer;
use App\Models\Market;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private const PAGE_VERSION_KEY = 'products.pages.version';

    public function __construct(
        private CloudinaryService $cloudinary,
        private NotificationService $notifications,
    ) {}

    /**
     * Clear every cached page. Call when a product, farmer, category, or market changes,
     * because that data is embedded in each page JSON.
     */
    public static function forgetListPages(): void
    {
        $version = (int) Cache::get(self::PAGE_VERSION_KEY, 1);
        Cache::forever(self::PAGE_VERSION_KEY, $version + 1);
    }

    /**
     * Fetch one product page. Already-loaded pages (same filters) are served from cache,
     * without re-querying. Other pages load only when the client requests them.
     */
    public function getPaginatedProducts(Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, (int) $request->input('per_page', 10));
        $cacheKey = $this->listCacheKey($request, $page, $perPage);
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && isset($cached['items'], $cached['total'])) {
            return $this->paginatorFromCache($cached, $perPage, $page);
        }

        $paginator = $this->productQuery($request)->paginate($perPage, ['*'], 'page', $page);

        Cache::put($cacheKey, [
            'total' => $paginator->total(),
            'items' => $paginator->getCollection()
                ->map(fn (Product $product) => $this->exportProduct($product))
                ->all(),
        ], now()->addDay());

        return $paginator;
    }

    private function productQuery(Request $request)
    {
        $query = Product::query()->with(['farmer.market', 'category']);

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        if ($request->filled('market_id')) {
            $marketId = $request->integer('market_id');
            $query->whereHas('farmer', function ($farmer) use ($marketId) {
                $farmer->where('market_id', $marketId);
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->boolean('in_stock')) {
            $query->where('stock_qty', '>', 0);
        }

        $sortBy = (string) $request->get('sort_by', 'created_at');
        $sortOrder = strtolower((string) $request->get('sort', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['created_at', 'price', 'name'];

        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortOrder)->orderBy('id', $sortOrder);
    }

    /**
     * @return array<string, mixed>
     */
    private function listFilters(Request $request): array
    {
        return [
            'keyword' => $request->input('keyword'),
            'farmer_id' => $request->input('farmer_id'),
            'market_id' => $request->input('market_id'),
            'category_id' => $request->input('category_id'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
            'in_stock' => $request->boolean('in_stock') ? 1 : 0,
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort' => $request->input('sort', 'desc'),
        ];
    }

    private function listCacheKey(Request $request, int $page, int $perPage): string
    {
        $version = (int) Cache::get(self::PAGE_VERSION_KEY, 1);
        $signature = md5(json_encode([$this->listFilters($request), $page, $perPage], JSON_THROW_ON_ERROR));

        return 'products.pages.'.$version.'.'.$signature;
    }

    /**
     * @param  array{total: int, items: array<int, array<string, mixed>>}  $cached
     */
    private function paginatorFromCache(array $cached, int $perPage, int $page): LengthAwarePaginator
    {
        $items = collect($cached['items'])->map(fn (array $row) => $this->importProduct($row));

        return new Paginator(
            $items,
            (int) $cached['total'],
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function exportProduct(Product $product): array
    {
        $farmer = $product->relationLoaded('farmer') ? $product->farmer : null;
        $market = $farmer?->relationLoaded('market') ? $farmer->market : null;

        return [
            'attributes' => $product->getAttributes(),
            'category' => $product->relationLoaded('category') && $product->category
                ? $product->category->getAttributes()
                : null,
            'farmer' => $farmer ? [
                'attributes' => $farmer->getAttributes(),
                'market' => $market?->getAttributes(),
            ] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function importProduct(array $row): Product
    {
        $product = (new Product)->newFromBuilder($row['attributes']);

        $product->setRelation(
            'category',
            isset($row['category']) ? (new Category)->newFromBuilder($row['category']) : null,
        );

        if (! isset($row['farmer'])) {
            $product->setRelation('farmer', null);

            return $product;
        }

        $farmer = (new Farmer)->newFromBuilder($row['farmer']['attributes']);
        $farmer->setRelation(
            'market',
            isset($row['farmer']['market']) ? (new Market)->newFromBuilder($row['farmer']['market']) : null,
        );
        $product->setRelation('farmer', $farmer);

        return $product;
    }

    /**
     * Create a product (also handles image upload if present).
     */
    public function createProduct(array $data)
    {
        $uploaded = [];

        try {
            return DB::transaction(function () use ($data, &$uploaded) {
                $data = $this->storeUploadedImages($data, $uploaded);

                return Product::create($data);
            });
        } catch (\Throwable $exception) {
            $this->cloudinary->deleteUrls($uploaded);

            throw $exception;
        }
    }

    /**
     * Update product details.
     */
    public function updateProduct(Product $product, array $data)
    {
        $uploaded = [];
        $previousStock = (int) $product->stock_qty;

        try {
            $product = DB::transaction(function () use ($product, $data, &$uploaded) {
                $data = $this->storeUploadedImages($data, $uploaded);

                $product->update($data);

                return $product->fresh(['farmer', 'category']);
            });
        } catch (\Throwable $exception) {
            $this->cloudinary->deleteUrls($uploaded);

            throw $exception;
        }

        if (array_key_exists('stock_qty', $data)) {
            $this->notifications->stockChanged($product, $previousStock, (int) $product->stock_qty);
        }

        return $product;
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product)
    {
        return DB::transaction(fn () => $product->delete());
    }

    /**
     * @param  array<int, string>  $uploaded
     */
    private function storeUploadedImages(array $data, array &$uploaded): array
    {
        if (! array_key_exists('image_url', $data) || ! is_array($data['image_url'])) {
            return $data;
        }

        $stored = [];

        foreach ($data['image_url'] as $image) {
            if ($image instanceof UploadedFile) {
                $url = $this->cloudinary->upload($image);
                $uploaded[] = $url;
                $stored[] = $url;

                continue;
            }

            if (is_string($image) && $image !== '') {
                $stored[] = $image;
            }
        }

        $data['image_url'] = array_values($stored);

        return $data;
    }
}
