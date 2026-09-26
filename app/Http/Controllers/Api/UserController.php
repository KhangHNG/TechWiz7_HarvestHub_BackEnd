<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
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
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

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
                'message' => 'Tạo người dùng thất bại: '.$e->getMessage(),
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
    public function update(UpdateUserRequest $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validated();

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
                'message' => 'Cập nhật người dùng thất bại: '.$e->getMessage(),
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
                'message' => 'Xóa người dùng thất bại: '.$e->getMessage(),
            ], 400);
        }
    }
}
