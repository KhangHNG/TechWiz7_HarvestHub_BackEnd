<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

class StoreAvatarRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'avatar' => 'Ảnh đại diện',
        ];
    }
}
