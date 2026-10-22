<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ChatRequest;
use App\Services\Ai\DeepSeekService;
use App\Services\Ai\GeminiService;
use Illuminate\Http\JsonResponse;

class AiChatController extends Controller
{
    public function __construct(
        private GeminiService $gemini,
        private ?DeepSeekService $deepSeek = null,
    ) {
        $this->deepSeek = $deepSeek ?? app(DeepSeekService::class);
    }

    public function chat(ChatRequest $request): JsonResponse
    {
        $data = $request->validated();
        $provider = $request->input('provider', config('services.ai.default_provider', 'gemini'));

        $service = match ($provider) {
            'deepseek' => $this->deepSeek,
            default => $this->gemini,
        };

        $result = $service->ask($data['message'], $data['history'] ?? []);

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
