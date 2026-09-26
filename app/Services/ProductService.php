<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
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
        return DB::transaction(function () use ($data) {
            $data = $this->storeUploadedImage($data);

            return Product::create($data);
        });
    }

    /**
     * Cập nhật thông tin sản phẩm.
     */
    public function updateProduct(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {
            $data = $this->storeUploadedImage($data, $product->image_url);

            $product->update($data);

            return $product->fresh(['farmer', 'category']);
        });
    }

    /**
     * Xóa sản phẩm.
     */
    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {
            $this->deleteStoredImage($product->image_url);

            return $product->delete();
        });
    }

    private function storeUploadedImage(array $data, ?string $previousPath = null): array
    {
        if (! isset($data['image_url']) || ! $data['image_url'] instanceof UploadedFile) {
            return $data;
        }

        if (! $data['image_url']->isValid()) {
            unset($data['image_url']);

            return $data;
        }

        $this->deleteStoredImage($previousPath);
        $data['image_url'] = $data['image_url']->store('products', 'public');

        return $data;
    }

    private function deleteStoredImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
