<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\ProductResource; // Khuyên dùng API Resource
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductsApiController extends Controller
{
    /**
     * Lấy danh sách sản phẩm (có tìm kiếm, lọc, sắp xếp, phân trang).
     *
     * GET /products
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Khởi tạo query kèm eager loading để tránh N+1 query
        $query = Product::query()->with(['farmer.market', 'category']);

        // 2. Lọc theo keyword (name hoặc description)
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                    ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        // 3. Lọc theo farmer_id
        if ($request->filled('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        // 4. Lọc theo category_id
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 5. Lọc theo khoảng giá (Rất cần thiết cho ứng dụng mua sắm)
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // 6. Sắp xếp linh hoạt (cho phép chọn cột sort: created_at, price, name...)
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort', 'desc'); // 'asc' hoặc 'desc'
        $allowedSorts = ['created_at', 'price', 'name'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // 7. Phân trang
        $perPage = $request->get('per_page', 10);
        $products = $query->paginate($perPage);

        // 8. Trả về JSON (Sử dụng ProductResource để chuẩn hóa dữ liệu sạch sẽ hơn)
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data'    => $products->items(), // Trả về danh sách product gốc từ Eloquent
            'meta'    => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'has_more'     => $products->hasMorePages(),
            ]
        ], 200);
    }

    /**
     * Chi tiết 1 sản phẩm kèm nông dân, chợ, danh mục.
     *
     * GET /products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::query()
            ->with(['farmer.market', 'farmer.user:id,full_name,phone', 'category'])
            ->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sản phẩm.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ], 200);
    }
}
