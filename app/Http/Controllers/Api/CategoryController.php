<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        // Collection response case (when calling ?all=true)
        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'All categories retrieved successfully.',
                'data' => CategoryResource::collection($categories),
            ], 200);
        }

        // Paginated response case
        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully.',
            'data' => CategoryResource::collection($categories),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
            ],
        ], 200);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        try {
            // 2. Call Service to handle create logic and image upload (if any)
            $category = $this->categoryService->createCategory($validatedData);

            // 3. Return success result with CategoryResource
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'data' => new CategoryResource($category),
            ], 201); // HTTP Status 201 Created

        } catch (\Exception $e) {
            // Handle exceptions if something goes wrong (e.g. storage errors)
            return response()->json([
                'success' => false,
                'message' => 'Failed to create category: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/categories/{id}
     */
    public function findById($id): JsonResponse
    {
        $category = Category::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Category retrieved successfully.',
            'data' => new CategoryResource($category),
        ], 200);
    }

    /**
     * PUT /api/categories/{id}
     */
    public function update(UpdateCategoryRequest $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validatedData = $request->validated();

        try {
            $category = $this->categoryService->updateCategory($category, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'data' => new CategoryResource($category),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/categories/{id}
     * Delete a product category.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // 1. Find category by ID; returns 404 automatically if not found
            $category = Category::findOrFail($id);

            // 2. Call Service to handle delete logic (including related-product checks and image deletion)
            $this->categoryService->deleteCategory($category);

            // 3. Return success result
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            // Catch exceptions (e.g. category still has products blocked by Service)
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete category: '.$e->getMessage(),
            ], 400); // Return 400 Bad Request for business-rule errors
        }
    }
}
