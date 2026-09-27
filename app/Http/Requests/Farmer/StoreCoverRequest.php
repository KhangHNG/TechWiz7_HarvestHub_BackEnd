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
            'cover' => 'Cover image',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'cover.max' => 'The cover image may not be greater than 1.5 MB.',
        ]);
    }
}
