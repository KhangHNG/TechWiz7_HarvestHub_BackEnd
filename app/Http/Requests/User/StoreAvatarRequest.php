<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

class StoreAvatarRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:1536'],
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar' => 'Ảnh đại diện',
        ];
    }

    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'avatar.max' => 'Ảnh đại diện không được vượt quá 1.5 MB.',
        ]);
    }
}
