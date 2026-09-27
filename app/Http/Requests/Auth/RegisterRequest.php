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
            'full_name' => 'Full name',
            'email' => 'Email',
            'phone' => 'Phone number',
            'password' => 'Password',
            'address' => 'Address',
            'city' => 'City',
            'district' => 'District',
            'capital' => 'Province / city',
            'role' => 'Role',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'email.email' => 'The email format is invalid.',
            'email.unique' => 'This email has already been taken.',
            'phone.digits' => 'The phone number must be numeric and exactly 10 digits.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);
    }
}
