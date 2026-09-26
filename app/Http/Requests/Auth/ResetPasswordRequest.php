<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class ResetPasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,filter', 'max:255'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'otp' => 'Mã OTP',
            'password' => 'Mật khẩu',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'otp.digits' => 'Mã OTP phải gồm 6 chữ số.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);
    }
}
