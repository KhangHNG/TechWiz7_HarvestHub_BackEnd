<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function attributes(): array
    {
        return [];
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'email' => 'The :attribute must be a valid email address.',
            'max.string' => 'The :attribute may not be greater than :max characters.',
            'max.array' => 'The :attribute may not have more than :max items.',
            'max.file' => 'The :attribute may not be greater than :max kilobytes.',
            'min.string' => 'The :attribute must be at least :min characters.',
            'min.numeric' => 'The :attribute must be at least :min.',
            'min.integer' => 'The :attribute must be at least :min.',
            'numeric' => 'The :attribute must be a number.',
            'integer' => 'The :attribute must be an integer.',
            'boolean' => 'The :attribute must be true or false.',
            'array' => 'The :attribute must be an array.',
            'in' => 'The :attribute is invalid.',
            'exists' => 'The :attribute does not exist.',
            'unique' => 'The :attribute has already been taken.',
            'image' => 'The :attribute must be an image.',
            'mimes' => 'The :attribute must be a file of type: :values.',
            'between.numeric' => 'The :attribute must be between :min and :max.',
            'regex' => 'The :attribute format is invalid.',
            'distinct' => 'The :attribute has a duplicate value.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Invalid data.',
            'errors' => $validator->errors(),
        ], 422));
    }

    protected function livingExists(string $table, string $column = 'id'): Exists
    {
        return Rule::exists($table, $column)
            ->where(fn ($query) => $query->whereNull('deleted_at'));
    }

    protected function livingCustomer(): Exists
    {
        return Rule::exists('users', 'id')->where(
            fn ($query) => $query->where('role', 'CUSTOMER')->whereNull('deleted_at')
        );
    }
}
