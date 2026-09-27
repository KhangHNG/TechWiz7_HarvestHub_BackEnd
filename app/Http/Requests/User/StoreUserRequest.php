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
                'string',
                'email:rfc,filter',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'phone' => ['required', 'digits:10'],
            'password' => ['required', 'string', 'min:6'],
            'address' => ['required', 'string'],
            'city' => ['required_unless:role,ADMIN', 'nullable', 'string', 'max:255'],
            'district' => ['required_unless:role,ADMIN', 'nullable', 'string', 'max:255'],
            'capital' => ['required_unless:role,ADMIN', 'nullable', 'string', 'max:255'],
            'role' => ['sometimes', Rule::in(['CUSTOMER', 'FARMER', 'ADMIN'])],
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
            'city.required_unless' => 'The city field is required.',
            'district.required_unless' => 'The district field is required.',
            'capital.required_unless' => 'The province/city field is required.',
        ]);
    }
}
