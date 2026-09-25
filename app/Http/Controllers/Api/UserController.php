<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * GET /api/users
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsers($request);

        if ($request->boolean('all')) {
            return response()->json([
                'success' => true,
                'message' => 'Lấy tất cả người dùng thành công.',
                'data' => UserResource::collection($users),
            ], 200);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách người dùng thành công.',
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/users
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6',
            'address' => 'nullable|string',
            'role' => 'nullable|in:CUSTOMER,FARMER,ADMIN',
        ]);

        try {
            $user = $this->userService->createUser($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Tạo người dùng thành công.',
                'data' => new UserResource($user),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo người dùng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/users/{id}
     */
    public function findById($id): JsonResponse
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Lấy người dùng thành công.',
            'data' => new UserResource($user),
        ], 200);
    }

    /**
     * PUT /api/users/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'full_name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'password' => 'nullable|string|min:6',
            'address' => 'nullable|string',
            'role' => 'nullable|in:CUSTOMER,FARMER,ADMIN',
        ]);

        try {
            $user = $this->userService->updateUser($user, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật người dùng thành công.',
                'data' => new UserResource($user),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật người dùng thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/users/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            $this->userService->deleteUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng thành công.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa người dùng thất bại: ' . $e->getMessage(),
            ], 400);
        }
    }
}
