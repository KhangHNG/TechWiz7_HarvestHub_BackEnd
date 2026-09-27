<?php

namespace App\Http\Requests\Farmer;

use App\Http\Requests\ApiFormRequest;

class StoreCoverRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'cover' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:1536'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cover' => 'Ảnh bìa',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'cover.max' => 'Ảnh bìa không được vượt quá 1.5 MB.',
        ]);
    }
}
