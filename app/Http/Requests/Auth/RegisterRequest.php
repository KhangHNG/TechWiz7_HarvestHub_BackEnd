<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email:rfc,filter',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'phone' => ['required', 'digits:10'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'capital' => ['required', 'string', 'max:255'],
            'role' => ['sometimes', Rule::in(['CUSTOMER', 'FARMER'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'full_name' => 'Họ tên',
            'email' => 'Email',
            'phone' => 'Số điện thoại',
            'password' => 'Mật khẩu',
            'address' => 'Địa chỉ',
            'city' => 'Thành phố',
            'district' => 'Quận / huyện',
            'capital' => 'Tỉnh / thành',
            'role' => 'Vai trò',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã tồn tại.',
            'phone.digits' => 'Số điện thoại phải là số và đủ 10 chữ số.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ]);
    }
}
