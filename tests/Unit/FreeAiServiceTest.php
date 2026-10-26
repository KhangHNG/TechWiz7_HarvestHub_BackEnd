<?php

namespace Tests\Unit;

use App\Services\Ai\AiToolResolver;
use App\Services\Ai\FreeAiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FreeAiServiceTest extends TestCase
{
    public function test_free_ai_missing_api_key_returns_error(): void
    {
        config(['services.free_ai.api_key' => null]);
        $service = new FreeAiService(new AiToolResolver);

        $result = $service->ask('Hello');
        $this->assertSame('FREE_AI_API_KEY is not configured.', $result['reply']);
        $this->assertSame([], $result['suggestions']);
    }

    public function test_free_ai_handles_insufficient_tokens_error(): void
    {
        config(['services.free_ai.api_key' => 'test-key']);
        config(['services.free_ai.base_url' => 'https://api.free.ai/v1']);

        Http::fake([
            'https://api.free.ai/v1/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Your free daily allowance does not cover this. Buy more tokens.',
                ],
            ], 402),
        ]);

        $service = new FreeAiService(new AiToolResolver);
        $result = $service->ask('Cho tôi hỏi về ổi');

        $this->assertStringContainsString('Free.ai không đủ token', $result['reply']);
    }

    public function test_free_ai_parses_normal_reply_and_suggestions(): void
    {
        config(['services.free_ai.api_key' => 'test-key']);
        config(['services.free_ai.base_url' => 'https://api.free.ai/v1']);

        Http::fake([
            'https://api.free.ai/v1/chat/completions' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'role' => 'assistant',
                            'content' => "Guava is available at Riverside Orchard.\n\nSUGGESTIONS:\n- Order guava from Riverside Orchard?\n- What is the price of king orange?",
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new FreeAiService(new AiToolResolver);
        $result = $service->ask('Is guava available?');

        $this->assertSame('Guava is available at Riverside Orchard.', $result['reply']);
        $this->assertSame([
            'Order guava from Riverside Orchard?',
            'What is the price of king orange?',
        ], $result['suggestions']);
    }
}
