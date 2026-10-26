<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FreeAiService implements AiServiceInterface
{
    public function __construct(private AiToolResolver $toolResolver) {}

    /**
     * History is only used for this request. Not persisted to the database.
     *
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array{reply: string, suggestions: array<int, string>}
     */
    public function ask(string $userPrompt, array $history = []): array
    {
        $vietnamese = $this->vietnamese($userPrompt);
        $apiKey = config('services.free_ai.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return self::splitSuggestions('FREE_AI_API_KEY is not configured.');
        }

        $systemPrompt = <<<'TXT'
You are the HarvestHub farm-market assistant. Reply briefly in English. Answer two kinds of questions, and never use the under-development sentence for them.

First, HarvestHub catalog facts: products, prices, stock, farms, farmers, markets, addresses, and pickup hours. Call a tool and answer only from the tool result. For which products exist, call list_products. For products of a named farmer, call list_products with farmer set to that name and omit keyword. For the price or stock of a named product, call get_product_stock. For the market, address, or pickup hours of a named farm, call get_pickup_slots. Catalog names are English, for example water spinach, cherry tomato, and mango. Pass that English name. If the first call returns not_found, call once more with another English name. Do not invent products, prices, stock, farm names, addresses, or hours. Keep stored names, addresses, hours, and prices unchanged. The tool result includes reply_language. Write the sentence in English. If the result is not_found, say it was not found.

Second, agricultural knowledge: how to store produce, nutrition such as foods rich in vitamin C, which produce is in season, and dishes or recipes that use agricultural products. Give a short answer from general knowledge. Before the final sentence, call list_products in the same step for each English catalog name that fits. For vitamin C, call list_products for guava, king orange, and cherry tomato. Mention only product_name and farmer_name from those tool results, and include the price or stock when the result has them. Ask if the user wants to order. Skip a name when its call returns not_found. Do not invent HarvestHub prices, stock, farm names, addresses, or hours.

For anything else, including weather, the current time, sports, and personal chat unrelated to produce, farmers, or farm food, do not call a tool. Reply with exactly "This feature is under development."

On every reply except exactly "This feature is under development.", end the message with this block and nothing after it:
SUGGESTIONS:
- Order guava from Riverside Orchard?
- What is the price of king orange?
Write 2 or 3 lines. Each line starts with "- " and is one question the user could send next. Each question must lead toward buying a product_name from a farmer_name that appeared in the latest tool result. Do not name a product or farm that was not in a tool result.
TXT;

        $messages = [];
        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt,
        ];

        foreach ($this->messagesFromHistory($history) as $historyTurn) {
            $messages[] = $historyTurn;
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userPrompt,
        ];

        $payload = [
            'model' => config('services.free_ai.model', 'qwen7b'),
            'messages' => $messages,
            'tools' => $this->toolResolver->getOpenAiToolsDeclaration(),
            'tool_choice' => 'auto',
        ];

        $response = $this->post($payload);
        if ($response->failed()) {
            return self::splitSuggestions($this->failureMessage($response, 'ask', $vietnamese));
        }

        for ($round = 0; $round < 2; $round++) {
            $choice = $response->json('choices.0') ?? [];
            $assistantMessage = $choice['message'] ?? [];
            $toolCalls = $assistantMessage['tool_calls'] ?? [];

            if (! is_array($toolCalls) || $toolCalls === []) {
                $content = $assistantMessage['content'] ?? null;

                return self::splitSuggestions(
                    is_string($content) && trim($content) !== ''
                        ? $content
                        : ($vietnamese
                            ? "Sorry, I didn't understand that."
                            : 'Sorry, I did not understand that.')
                );
            }

            // Append assistant tool request message
            $cleanAssistantMessage = [
                'role' => 'assistant',
                'tool_calls' => $toolCalls,
            ];
            if (isset($assistantMessage['content']) && $assistantMessage['content'] !== null) {
                $cleanAssistantMessage['content'] = $assistantMessage['content'];
            }
            $messages[] = $cleanAssistantMessage;

            foreach ($toolCalls as $toolCall) {
                $functionName = $toolCall['function']['name'] ?? '';
                $rawArgs = $toolCall['function']['arguments'] ?? '{}';
                $args = is_string($rawArgs) ? json_decode($rawArgs, true) : (array) $rawArgs;
                if (! is_array($args)) {
                    $args = [];
                }

                $dbResult = $this->toolResolver->execute($functionName, $args);
                $toolResponse = array_merge($dbResult, [
                    'reply_language' => 'en',
                ]);

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall['id'] ?? '',
                    'content' => json_encode($toolResponse, JSON_UNESCAPED_UNICODE),
                ];
            }

            $response = $this->post([
                'model' => config('services.free_ai.model', 'qwen7b'),
                'messages' => $messages,
                'tools' => $this->toolResolver->getOpenAiToolsDeclaration(),
                'tool_choice' => 'auto',
            ]);

            if ($response->failed()) {
                return self::splitSuggestions($this->failureMessage($response, 'synthesize', $vietnamese));
            }
        }

        $finalContent = $response->json('choices.0.message.content');

        return self::splitSuggestions(
            is_string($finalContent) && trim($finalContent) !== ''
                ? $finalContent
                : ($vietnamese
                    ? 'Checked produce information.'
                    : 'Checked the product information.')
        );
    }

    /**
     * @return array{reply: string, suggestions: array<int, string>}
     */
    public static function splitSuggestions(string $text): array
    {
        return GeminiService::splitSuggestions($text);
    }

    private function vietnamese(string $text): bool
    {
        return (bool) preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/iu', $text);
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    private function messagesFromHistory(array $history): array
    {
        $messages = [];

        foreach (array_slice($history, -12) as $turn) {
            $role = in_array($turn['role'] ?? '', ['model', 'assistant'], true) ? 'assistant' : 'user';
            $text = trim((string) ($turn['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $messages[] = [
                'role' => $role,
                'content' => $text,
            ];
        }

        return $messages;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function post(array $payload): Response
    {
        $baseUrl = rtrim((string) config('services.free_ai.base_url', 'https://api.free.ai/v1'), '/');
        $url = "{$baseUrl}/chat/completions";
        $apiKey = (string) config('services.free_ai.api_key');

        $response = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($url, $payload);

            if (! in_array($response->status(), [429, 503], true)) {
                return $response;
            }

            usleep(500000 * ($attempt + 1));
        }

        return $response;
    }

    private function failureMessage(Response $response, string $step, bool $vietnamese): string
    {
        $body = $response->json();
        $message = (string) ($body['error']['message'] ?? $body['error'] ?? '');

        Log::error("FreeAI {$step}", [
            'status' => $response->status(),
            'body' => $body,
        ]);

        if (
            str_contains(strtolower($message), 'insufficient') ||
            str_contains(strtolower($message), 'tokens') ||
            str_contains(strtolower($message), 'premium') ||
            $response->status() === 402
        ) {
            return $vietnamese
                ? 'Tài khoản Free.ai không đủ token hoặc cần nạp thêm credit. Vui lòng kiểm tra tại free.ai/account.'
                : 'Free.ai account has insufficient balance/tokens. Please check free.ai/account.';
        }

        if (in_array($response->status(), [429, 503], true)) {
            return $vietnamese
                ? 'The AI assistant is overloaded, please try again shortly.'
                : 'The assistant is busy. Please try again in a moment.';
        }

        return $vietnamese
            ? 'Sorry, the AI assistant is currently unavailable.'
            : 'Sorry, the assistant is unavailable right now.';
    }
}
