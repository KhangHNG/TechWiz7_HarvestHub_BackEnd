<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProductService
{
    /**
     * Lấy danh sách sản phẩm có phân trang, tìm kiếm, lọc.
     */
    public function getPaginatedProducts(Request $request)
    {
        $query = Product::query()->with(['farmer', 'category']);

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
            // Xử lý lưu ảnh nếu request có gửi file hình ảnh
            if (isset($data['image']) && $data['image']->isValid()) {
                $data['image'] = $data['image']->store('products', 'public');
            }

            return Product::create($data);
        });
    }

    /**
     * Cập nhật thông tin sản phẩm.
     */
    public function updateProduct(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {
            // Nếu có ảnh mới thì xóa ảnh cũ và lưu ảnh mới
            if (isset($data['image']) && $data['image']->isValid()) {
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $data['image'] = $data['image']->store('products', 'public');
            }

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
            // Xóa file ảnh vật lý nếu có
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            return $product->delete();
        });
    }
}
