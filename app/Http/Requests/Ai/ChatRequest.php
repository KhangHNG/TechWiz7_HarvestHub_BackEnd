<?php

namespace App\Http\Requests\Ai;

use App\Http\Requests\ApiFormRequest;

class ChatRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:2000'],
            'history' => ['sometimes', 'array', 'max:20'],
            'history.*.role' => ['required', 'in:user,model'],
            'history.*.text' => ['required', 'string', 'max:4000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'message' => 'Tin nhắn',
            'history' => 'Lịch sử hội thoại',
            'history.*.role' => 'Vai trò',
            'history.*.text' => 'Nội dung hội thoại',
        ];
    }
}
