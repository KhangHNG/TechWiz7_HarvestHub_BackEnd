<?php

namespace App\Http\Requests\Notification;

use App\Http\Requests\ApiFormRequest;

class StoreDeviceTokenRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'max:512'],
            'platform' => ['required', 'in:android,ios'],
        ];
    }

    public function attributes(): array
    {
        return [
            'fcm_token' => 'Token thiết bị',
            'platform' => 'Nền tảng',
        ];
    }
}
