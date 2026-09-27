<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Category name',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.unique' => 'This category name has already been taken.',
        ]);
    }
}
