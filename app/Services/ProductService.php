<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private CloudinaryService $cloudinary,
        private NotificationService $notifications,
    ) {}

    /**
     * Lấy danh sách sản phẩm có phân trang, tìm kiếm, lọc.
     */
    public function getPaginatedProducts(Request $request)
    {
        $query = Product::query()->with(['farmer.market', 'category']);

        // 1. Tìm kiếm theo keyword (name hoặc description)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        // 2. Lọc theo farmer_id
        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        // 3. Lọc theo category_id
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 4. Lọc theo khoảng giá
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // 5. Sắp xếp
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort', 'desc');
        $allowedSorts = ['created_at', 'price', 'name'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // 6. Phân trang
        $perPage = $request->get('per_page', 10);

        return $query->paginate($perPage);
    }

    /**
     * Tạo mới sản phẩm (Xử lý cả logic upload ảnh nếu có).
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
     * Cập nhật thông tin sản phẩm.
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
     * Xóa sản phẩm.
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
