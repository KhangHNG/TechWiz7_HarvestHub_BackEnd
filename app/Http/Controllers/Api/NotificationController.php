<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = UserNotification::query()
            ->where('user_id', JWTAuth::user()->id)
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách thông báo thành công.',
            'data' => $notifications->getCollection()->map(fn (UserNotification $notification) => $this->payload($notification))->values(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    public function read(int $id): JsonResponse
    {
        $notification = UserNotification::query()
            ->where('user_id', JWTAuth::user()->id)
            ->find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông báo.',
            ], 404);
        }

        if ($notification->read_at === null) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã đánh dấu đã đọc.',
            'data' => $this->payload($notification),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(UserNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'body' => $notification->body,
            'data' => $notification->data,
            'read_at' => $notification->read_at,
            'created_at' => $notification->created_at,
        ];
    }
}
