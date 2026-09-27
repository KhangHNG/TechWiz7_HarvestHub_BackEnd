<?php
namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    /**
     * List categories (supports search, pagination, or fetch-all for the mobile app).
     */
    public function getCategories(Request $request)
    {
        $query = Category::query();

        // 1. Search by category name
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where('name', 'LIKE', "%{$keyword}%");
        }

        // 2. If the request asks for all (no pagination — used for dropdowns or mobile menus)
        if ($request->boolean('all')) {
            $categories = $query->orderBy('name', 'asc')->get();

            return $this->withSampleImages($request, $categories);
        }

        // 3. Default is paginated (used for the admin page)
        $perPage = $request->get('per_page', 10);
        $categories = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->withSampleImages($request, $categories);
    }

    /**
     * Newest product photo per category, matching the catalog walk order.
     *
     * @param  \Illuminate\Support\Collection<int, Category>|\Illuminate\Contracts\Pagination\LengthAwarePaginator<int, Category>  $categories
     */
    private function withSampleImages(Request $request, mixed $categories): mixed
    {
        if (! $request->boolean('with_sample_image')) {
            return $categories;
        }

        $images = [];
        $products = Product::query()
            ->whereNotNull('image_url')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get(['category_id', 'image_url']);

        foreach ($products as $product) {
            $categoryId = (int) $product->category_id;
            if (array_key_exists($categoryId, $images)) {
                continue;
            }

            $url = Product::imageList($product->image_url)[0] ?? null;
            if (! is_string($url) || $url === '') {
                continue;
            }

            if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
                $url = url($url);
            }

            $images[$categoryId] = $url;
        }

        $attach = function (Category $category) use ($images): void {
            $category->setAttribute('sample_image_url', $images[(int) $category->id] ?? null);
        };

        if ($categories instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $categories->getCollection()->each($attach);
        } else {
            $categories->each($attach);
        }

        return $categories;
    }

    /**
     * Create a category (also handles icon/thumbnail image if present).
     */
    public function createCategory(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Upload icon/thumbnail image if provided
            if (isset($data['image']) && $data['image']->isValid()) {
                $data['image'] = $data['image']->store('categories', 'public');
            }

            return Category::create($data);
        });
    }

    /**
     * Update category details.
     */
    public function updateCategory(Category $category, array $data)
    {
        return DB::transaction(function () use ($category, $data) {
            // If there is a new image, delete the old one from storage and save the new one
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
     * Delete a category.
     */
    public function deleteCategory(Category $category)
    {
        return DB::transaction(function () use ($category) {
            // Check whether the category still has products (business rule)
            if ($category->products()->count() > 0) {
                throw new \Exception('Cannot delete a category that still has products.');
            }

            // Delete the physical image file if present
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            return $category->delete();
        });
    }
}
