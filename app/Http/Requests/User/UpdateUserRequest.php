<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'required',
                'string',
                'email:rfc,filter',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($this->route('id'))
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'phone' => ['sometimes', 'required', 'digits:10'],
            'password' => ['sometimes', 'nullable', 'string', 'min:6'],
            'address' => ['sometimes', 'required', 'string'],
            'city' => ['sometimes', 'nullable', 'string', 'max:255'],
            'district' => ['sometimes', 'nullable', 'string', 'max:255'],
            'capital' => ['sometimes', 'nullable', 'string', 'max:255'],
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
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $user = User::query()->find($this->route('id'));
            $role = $this->input('role', $user?->role ?? 'CUSTOMER');

            if (! in_array($role, ['CUSTOMER', 'FARMER'], true)) {
                return;
            }

            foreach ([
                'city' => 'The city field is required.',
                'district' => 'The district field is required.',
                'capital' => 'The province/city field is required.',
            ] as $field => $message) {
                $value = $this->exists($field) ? $this->input($field) : $user?->{$field};

                if (blank(is_string($value) ? trim($value) : $value)) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
