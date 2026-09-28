<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ChatRequest;
use App\Services\Ai\GeminiService;
use Illuminate\Http\JsonResponse;

class AiChatController extends Controller
{
    public function __construct(private GeminiService $gemini) {}

    public function chat(ChatRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->gemini->ask($data['message'], $data['history'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Replied',
            'data' => [
                'reply' => $result['reply'],
                'suggestions' => $result['suggestions'],
            ],
        ]);
    }
}
