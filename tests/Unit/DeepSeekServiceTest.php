<?php

namespace Tests\Unit;

use App\Services\Ai\AiToolResolver;
use App\Services\Ai\DeepSeekService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeepSeekServiceTest extends TestCase
{
    public function test_deepseek_missing_api_key_returns_error(): void
    {
        config(['services.deepseek.api_key' => null]);
        $service = new DeepSeekService(new AiToolResolver);

        $result = $service->ask('Hello');
        $this->assertSame('DEEPSEEK_API_KEY is not configured.', $result['reply']);
        $this->assertSame([], $result['suggestions']);
    }

    public function test_deepseek_handles_insufficient_balance_error(): void
    {
        config(['services.deepseek.api_key' => 'test-key']);
        Http::fake([
            'https://api.deepseek.com/chat/completions' => Http::response([
                'error' => [
                    'message' => 'Insufficient Balance',
                    'type' => 'unknown_error',
                ],
            ], 402),
        ]);

        $service = new DeepSeekService(new AiToolResolver);
        $result = $service->ask('Cho tôi hỏi về ổi');

        $this->assertStringContainsString('Insufficient Balance', $result['reply']);
    }

    public function test_deepseek_parses_normal_reply_and_suggestions(): void
    {
        config(['services.deepseek.api_key' => 'test-key']);
        Http::fake([
            'https://api.deepseek.com/chat/completions' => Http::response([
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

        $service = new DeepSeekService(new AiToolResolver);
        $result = $service->ask('Is guava available?');

        $this->assertSame('Guava is available at Riverside Orchard.', $result['reply']);
        $this->assertSame([
            'Order guava from Riverside Orchard?',
            'What is the price of king orange?',
        ], $result['suggestions']);
    }
}
