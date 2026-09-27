<?php

namespace App\Http\Requests\Farmer;

use App\Http\Requests\ApiFormRequest;

class StoreCoverRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'cover' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cover' => 'Ảnh bìa',
        ];
    }
}
