<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthService
{
    public function __construct(private UserService $users) {}

    public function register(array $data): User
    {
        unset($data['password_confirmation']);
        $data['role'] = $data['role'] ?? 'CUSTOMER';

        $user = $this->users->createUser($data);

        try {
            $this->sendOtp($user->email, 'verify');
        } catch (\Throwable $e) {
            report($e);
            $user->delete();

            throw new \RuntimeException('Không gửi được email xác thực. Vui lòng thử lại.', 500);
        }

        return $user;
    }

    public function verifyEmail(string $email, string $otp): User
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! $this->consumeOtp($email, 'verify', $otp)) {
            throw new \RuntimeException('Mã OTP không đúng hoặc đã hết hạn.', 400);
        }

        $user->email_verified_at = now();
        $user->save();

        return $user->fresh();
    }

    public function resendVerification(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            throw new \RuntimeException('Không tìm thấy tài khoản với email này.', 404);
        }

        if ($user->email_verified_at !== null) {
            throw new \RuntimeException('Email đã được xác thực.', 400);
        }

        $this->sendOtp($user->email, 'verify');
    }

    public function sendResetOtp(string $email): void
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return;
        }

        $this->sendOtp($user->email, 'reset');
    }

    public function resetPassword(string $email, string $otp, string $password): void
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! $this->consumeOtp($email, 'reset', $otp)) {
            throw new \RuntimeException('Mã OTP không đúng hoặc đã hết hạn.', 400);
        }

        $user->password_hash = $password;
        $user->save();
    }

    /**
     * @return array{access_token: string, token_type: string, expires_in: int, user: array{id: int, full_name: string, email: string, role: string}}
     */
    public function tokenPayload(User $user, string $token): array
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ];
    }

    public function loginToken(User $user): string
    {
        return Auth::guard('api')->login($user);
    }

    private function sendOtp(string $email, string $purpose): void
    {
        EmailOtp::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->delete();

        $code = (string) random_int(100000, 999999);

        EmailOtp::query()->create([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($email)->send(new OtpMail($code, $purpose));
    }

    private function consumeOtp(string $email, string $purpose, string $otp): bool
    {
        $record = EmailOtp::query()
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->latest('id')
            ->first();

        if (! $record) {
            return false;
        }

        if ($record->expires_at->isPast() || ! Hash::check($otp, $record->code_hash)) {
            return false;
        }

        $record->delete();

        return true;
    }
}
