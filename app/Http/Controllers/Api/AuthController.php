<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(private AuthService $auth) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->auth->register($request->validated());
        } catch (\RuntimeException $e) {
            return $this->error($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công. Mã xác thực đã được gửi tới email của bạn.',
            'data' => [
                'email' => $user->email,
            ],
        ], 201);
    }

    public function verifyEmail(VerifyEmailRequest $request): JsonResponse
    {
        try {
            $user = $this->auth->verifyEmail($request->validated('email'), $request->validated('otp'));
        } catch (\RuntimeException $e) {
            return $this->error($e);
        }

        $token = $this->auth->loginToken($user);

        return response()->json([
            'success' => true,
            'message' => 'Xác thực email thành công',
            'data' => $this->auth->tokenPayload($user, $token),
        ]);
    }

    public function resendVerification(ResendVerificationRequest $request): JsonResponse
    {
        try {
            $this->auth->resendVerification($request->validated('email'));
        } catch (\RuntimeException $e) {
            return $this->error($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Mã xác thực đã được gửi tới email của bạn.',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->auth->sendResetOtp($request->validated('email'));
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Không gửi được email. Vui lòng thử lại.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nếu email tồn tại, mã OTP đặt lại mật khẩu đã được gửi.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $this->auth->resetPassword($data['email'], $data['otp'], $data['password']);
        } catch (\RuntimeException $e) {
            return $this->error($e);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công.',
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ], 401);
        }

        $user = Auth::guard('api')->user();

        if ($user->email_verified_at === null) {
            try {
                Auth::guard('api')->logout();
            } catch (\Throwable $e) {
                report($e);
            }

            return response()->json([
                'success' => false,
                'message' => 'Email chưa được xác thực. Vui lòng nhập mã OTP đã gửi tới email của bạn.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => $this->auth->tokenPayload($user, $token),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    private function error(\RuntimeException $e): JsonResponse
    {
        $status = $e->getCode();

        if ($status < 400 || $status > 599) {
            $status = 400;
        }

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $status);
    }
}
