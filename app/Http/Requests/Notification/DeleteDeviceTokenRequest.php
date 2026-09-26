<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\ApiFormRequest;

class DeleteDeviceTokenRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'max:512'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fcm_token' => 'Token thiết bị',
        ];
    }
}
