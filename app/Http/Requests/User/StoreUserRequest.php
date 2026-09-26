<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'phone' => ['required', 'string', 'regex:/^0\d{9}$/'],
            'password' => ['required', 'string', 'min:6'],
            'address' => ['required', 'string'],
            'role' => ['sometimes', Rule::in(['CUSTOMER', 'FARMER', 'ADMIN'])],
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
            'role' => 'Vai trò',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.unique' => 'Email này đã tồn tại.',
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 0.',
        ]);
    }
}
