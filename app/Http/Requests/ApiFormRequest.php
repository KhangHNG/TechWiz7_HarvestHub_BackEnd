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
            'required' => ':attribute không được để trống.',
            'string' => ':attribute phải là chuỗi.',
            'email' => ':attribute không đúng định dạng email.',
            'max.string' => ':attribute không được vượt quá :max ký tự.',
            'max.array' => ':attribute không được vượt quá :max phần tử.',
            'max.file' => ':attribute không được vượt quá :max kilobyte.',
            'min.string' => ':attribute phải có ít nhất :min ký tự.',
            'min.numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
            'min.integer' => ':attribute phải lớn hơn hoặc bằng :min.',
            'numeric' => ':attribute phải là số.',
            'integer' => ':attribute phải là số nguyên.',
            'boolean' => ':attribute phải là true hoặc false.',
            'array' => ':attribute phải là mảng.',
            'in' => ':attribute không hợp lệ.',
            'exists' => ':attribute không tồn tại.',
            'unique' => ':attribute đã tồn tại.',
            'image' => ':attribute phải là hình ảnh.',
            'mimes' => ':attribute phải có định dạng: :values.',
            'between.numeric' => ':attribute phải nằm trong khoảng :min đến :max.',
            'regex' => ':attribute không đúng định dạng.',
            'distinct' => ':attribute bị trùng.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Dữ liệu không hợp lệ.',
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
