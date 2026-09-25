<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\CategoryService;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * GET /api/categories
     */
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories($request);

        // Trường hợp trả về dạng Collection (khi gọi ?all=true)
        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả danh mục thành công.',
                'data'    => CategoryResource::collection($categories),
            ], 200);
        }

        // Trường hợp trả về phân trang
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách danh mục thành công.',
            'data'    => CategoryResource::collection($categories),
            'meta'    => [
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
                'per_page'     => $categories->perPage(),
                'total'        => $categories->total(),
            ]
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Validate dữ liệu đầu vào
        $validatedData = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'Tên danh mục không được để trống.',
            'name.unique'   => 'Tên danh mục này đã tồn tại.',
        ]);

        try {
            // 2. Gọi Service để xử lý logic tạo mới và upload ảnh (nếu có)
            $category = $this->categoryService->createCategory($validatedData);

            // 3. Trả về kết quả thành công với CategoryResource
            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục mới thành công.',
                'data'    => new CategoryResource($category),
            ], 201); // HTTP Status 201 Created

        } catch (\Exception $e) {
            // Xử lý bắt lỗi nếu có sự cố phát sinh (ví dụ: lỗi lưu storage)
            return response()->json([
                'success' => false,
                'message' => 'Tạo danh mục thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/categories/{id}
     * Xóa một danh mục sản phẩm.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // 1. Tìm danh mục theo ID, nếu không thấy sẽ tự động trả về lỗi 404
            $category = Category::findOrFail($id);

            // 2. Gọi Service để xử lý logic xóa (bao gồm kiểm tra sản phẩm liên quan và xóa ảnh)
            $this->categoryService->deleteCategory($category);

            // 3. Trả về kết quả thành công
            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công.',
            ], 200);

        } catch (\Exception $e) {
            // Bắt lỗi ngoại lệ (ví dụ: danh mục vẫn còn chứa sản phẩm do Service chặn lại)
            return response()->json([
                'success' => false,
                'message' => 'Xóa danh mục thất bại: ' . $e->getMessage(),
            ], 400); // Trả về mã lỗi 400 Bad Request cho các lỗi liên quan đến nghiệp vụ
        }
    }
}
