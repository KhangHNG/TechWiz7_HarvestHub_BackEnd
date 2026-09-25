<?php
namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    /**
     * Lấy danh sách danh mục (Hỗ trợ tìm kiếm, phân trang hoặc lấy tất cả cho App mobile).
     */
    public function getCategories(Request $request)
    {
        $query = Category::query();

        // 1. Tìm kiếm theo tên danh mục
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // 2. Nếu request yêu cầu lấy tất cả (không phân trang - dùng cho dropdown hoặc menu mobile)
        if ($request->boolean('all')) {
            return $query->orderBy('name', 'asc')->get();
        }

        // 3. Mặc định có phân trang (dùng cho trang quản trị Admin)
        $perPage = $request->get('per_page', 10);
        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Tạo mới danh mục (Xử lý cả ảnh icon/thumbnail nếu có).
     */
    public function createCategory(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Xử lý upload ảnh icon/thumbnail nếu có gửi lên
            if (isset($data['image']) && $data['image']->isValid()) {
                $data['image'] = $data['image']->store('categories', 'public');
            }

            return Category::create($data);
        });
    }

    /**
     * Cập nhật thông tin danh mục.
     */
    public function updateCategory(Category $category, array $data)
    {
        return DB::transaction(function () use ($category, $data) {
            // Nếu có ảnh mới thì xóa ảnh cũ trên storage và lưu ảnh mới
            if (isset($data['image']) && $data['image']->isValid()) {
                if ($category->image && Storage::disk('public')->exists($category->image)) {
                    Storage::disk('public')->delete($category->image);
                }
                $data['image'] = $data['image']->store('categories', 'public');
            }

            $category->update($data);
            return $category->fresh();
        });
    }

    /**
     * Xóa danh mục.
     */
    public function deleteCategory(Category $category)
    {
        return DB::transaction(function () use ($category) {
            // Kiểm tra xem danh mục có sản phẩm nào không (tùy chọn nghiệp vụ)
            if ($category->products()->count() > 0) {
                throw new \Exception('Không thể xóa danh mục đang chứa sản phẩm.');
            }

            // Xóa file ảnh vật lý nếu có
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            return $category->delete();
        });
    }
}
