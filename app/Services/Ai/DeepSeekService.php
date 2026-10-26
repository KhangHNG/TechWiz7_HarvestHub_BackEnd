<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService implements AiServiceInterface
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
        $apiKey = config('services.deepseek.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return self::splitSuggestions('DEEPSEEK_API_KEY is not configured.');
        }

        $systemPrompt = <<<'TXT'
You are the HarvestHub farm-market assistant. Reply briefly in English (or in Vietnamese if the user asked in Vietnamese). Answer all questions related to produce, farms, farmers, catalog, nutrition, vitamins, recipes, and agriculture. Never use the under-development sentence for any of these!

First, HarvestHub catalog facts and farm queries:
- For which products exist, product categories (e.g. fruits, vegetables, herbs, grains, dairy, mushrooms), or what products a farm has: call list_products.
  - To list products of a named farm (e.g. Han River Produce, Green Leaf Farm, Riverside Orchard, Koyambedu Harvest), call list_products with farmer set to that farm name.
  - To list products by category, call list_products with keyword set to the category (e.g. "fruits", "vegetables").
- For the price or stock of a named product: call get_product_stock.
- For pickup locations, market address, or hours of a farm: call get_pickup_slots with the farm name.
- Catalog names are English, for example Tomato, Cherry tomato, Carrot, Pumpkin, Hoa Loc mango, Water spinach, Guava, King orange. Do not invent products, prices, stock, farm names, addresses, or hours.

Second, produce nutrition, vitamins, health benefits, and recipes:
- For questions about produce storage, seasonal produce, recipes, or nutrition (such as produce rich in Vitamin A, Vitamin C, Vitamin B, Vitamin E, potassium, fiber):
  - Give a helpful explanation from general agricultural knowledge.
  - Call list_products with the nutrient (e.g. keyword: "vitamin A", "vitamin C", "potassium", "fiber") or for each fitting English produce name (e.g. for Vitamin A: carrot, pumpkin, tomato, cherry tomato, mango; for Vitamin C: guava, king orange, green pomelo, cherry tomato).
  - In your response text, you MUST explicitly mention the exact catalog product names (e.g. "Tomato", "Cherry tomato", "Carrot", "Pumpkin", "Hoa Loc mango") along with their farmer_name and price or stock from the tool results. Explicitly writing these catalog product names in your text allows the mobile app to display interactive product cards for the customer!
  - Ask if the user wants to order.

Third, unrelated topics:
- ONLY for topics completely unrelated to produce, farms, markets, food, cooking, nutrition, or agriculture (such as sports scores, live weather forecast, politics, computer coding, or personal chit-chat unrelated to food/farming), do not call a tool. Reply with exactly:
"This feature is under development."

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
            'model' => config('services.deepseek.model', 'deepseek-chat'),
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
                'model' => config('services.deepseek.model', 'deepseek-chat'),
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
        $baseUrl = rtrim((string) config('services.deepseek.base_url', 'https://api.deepseek.com'), '/');
        $url = "{$baseUrl}/chat/completions";
        $apiKey = (string) config('services.deepseek.api_key');

        $response = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = Http::timeout(25)
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
        $message = (string) ($body['error']['message'] ?? '');

        Log::error("DeepSeek {$step}", [
            'status' => $response->status(),
            'body' => $body,
        ]);

        if (str_contains(strtolower($message), 'insufficient balance') || $response->status() === 402) {
            return $vietnamese
                ? 'Tài khoản DeepSeek không đủ số dư (Insufficient Balance). Vui lòng nạp thêm credit.'
                : 'DeepSeek account has insufficient balance. Please top up your credits.';
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
