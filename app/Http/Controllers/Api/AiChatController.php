<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ai\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function __construct(private GeminiService $gemini) {}

    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'sometimes|array|max:20',
            'history.*.role' => 'required|in:user,model',
            'history.*.text' => 'required|string|max:4000',
        ]);

        $reply = $this->gemini->ask($data['message'], $data['history'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Đã trả lời',
            'data' => [
                'reply' => $reply,
            ],
        ]);
    }
}
