<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendVerificationRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
            'message' => 'Registration successful. A verification email has been sent. Please open the link in the email.',
            'data' => [
                'email' => $user->email,
            ],
        ], 201);
    }

    public function verifyFromLink(int $id, string $hash): View
    {
        try {
            $this->auth->verifyFromSignedLink($id, $hash);
        } catch (\RuntimeException $e) {
            return view('auth.email-verified', [
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return view('auth.email-verified', [
            'success' => true,
            'message' => 'Email verified. You can sign in.',
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
            'message' => 'A verification email has been sent. Please open the link in the email.',
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
                'message' => 'Could not send email. Please try again.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'If the email exists, a password reset OTP has been sent.',
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
            'message' => 'Password reset successful.',
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect email or password.',
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
                'message' => 'Email is not verified. Please open the link in the verification email.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Signed in successfully',
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
                'avatar_url' => $user->avatar_url,
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
