<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $productService;

    // Inject ProductService vào Controller thông qua Dependency Injection
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * GET /api/products
     */
    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->getPaginatedProducts($request);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sản phẩm thành công.',
            'data'    => ProductResource::collection($products),
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
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        // Bạn có thể validate ở đây hoặc dùng FormRequest riêng trong app/Http/Requests/Api/
        $validatedData = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:1',
            'farmer_id'   => 'required|exists:farmers,id',
            'category_id' => 'required|exists:categories,id',
            'stock_qty'   => 'required|numeric|min:1',
            'image_url'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $product = $this->productService->createProduct($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Tạo sản phẩm thành công.',
            'data'    => new ProductResource($product),
        ], 201);
    }

    /**
     * DELETE /api/products/{id}
     * Xóa một sản phẩm.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // 1. Tìm sản phẩm theo ID, nếu không thấy sẽ tự động trả về lỗi 404
            $product = Product::findOrFail($id);

            // 2. Gọi ProductService để xử lý logic xóa (bao gồm cả xóa file ảnh vật lý trên storage)
            $this->productService->deleteProduct($product);

            // 3. Trả về kết quả thành công
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công.',
            ], 200);

        } catch (\Exception $e) {
            // Bắt lỗi ngoại lệ nếu có sự cố phát sinh trong quá trình xóa
            return response()->json([
                'success' => false,
                'message' => 'Xóa sản phẩm thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }
}
