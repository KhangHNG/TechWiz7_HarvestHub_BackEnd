<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;

class VerifyEmailRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc,filter', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'Email',
            'otp' => 'Mã OTP',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'otp.digits' => 'Mã OTP phải gồm 6 chữ số.',
        ]);
    }
}
