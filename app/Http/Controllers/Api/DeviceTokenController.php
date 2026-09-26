<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\DeleteDeviceTokenRequest;
use App\Http\Requests\Notification\StoreDeviceTokenRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class DeviceTokenController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $token = $this->notifications->registerToken(
            JWTAuth::user(),
            $request->validated('fcm_token'),
            $request->validated('platform'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu token thông báo.',
            'data' => [
                'id' => $token->id,
                'platform' => $token->platform,
            ],
        ]);
    }

    public function destroy(DeleteDeviceTokenRequest $request): JsonResponse
    {
        $this->notifications->deleteToken(JWTAuth::user(), $request->validated('fcm_token'));

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa token thông báo.',
        ]);
    }
}
