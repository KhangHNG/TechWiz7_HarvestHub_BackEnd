<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function __construct(private AiToolResolver $toolResolver) {}

    /**
     * History is only used for this request. Not persisted to the database.
     *
     * @param  array<int, array{role: string, text: string}>  $history
     */
    public function ask(string $userPrompt, array $history = []): string
    {
        $vietnamese = $this->vietnamese($userPrompt);
        $apiKey = config('services.gemini.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return $vietnamese
                ? 'GEMINI_API_KEY is not configured.'
                : 'GEMINI_API_KEY is not configured.';
        }

        $systemInstruction = [
            'parts' => [[
                'text' => 'You are the HarvestHub farm-market assistant. Reply briefly in English. Answer two kinds of questions, and never use the under-development sentence for them. First, HarvestHub catalog facts: products, prices, stock, farms, farmers, markets, addresses, and pickup hours. Call a tool and answer only from the tool result. For which products exist, call list_products. For products of a named farmer, call list_products with farmer set to that name and omit keyword. For the price or stock of a named product, call get_product_stock. For the market, address, or pickup hours of a named farm, call get_pickup_slots. Catalog names are English, for example water spinach, cherry tomato, and mango. Pass that English name. If the first call returns not_found, call once more with another English name. Do not invent products, prices, stock, farm names, addresses, or hours. Keep stored names, addresses, hours, and prices unchanged. The tool result includes reply_language. Write the sentence in English. If the result is not_found, say it was not found. Second, agricultural knowledge that does not need the catalog: how to store produce, nutrition such as foods rich in vitamin C, which produce is in season, and dishes or recipes that use agricultural products. Examples: "How to store leafy greens", "Foods rich in vitamin C", "Seasonal produce". Answer these from general knowledge and do not call a tool. Do not invent HarvestHub prices, stock, farm names, addresses, or hours. For anything else, including weather, the current time, sports, and personal chat unrelated to produce, farmers, or farm food, do not call a tool. Reply with exactly "This feature is under development."',
            ]],
        ];

        $contents = $this->contentsFromHistory($history);
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userPrompt]],
        ];

        $payload = [
            'system_instruction' => $systemInstruction,
            'contents' => $contents,
            'tools' => $this->toolResolver->getToolsDeclaration(),
        ];

        $response = $this->post($payload);
        if ($response->failed()) {
            return $this->failureMessage($response, 'ask', $vietnamese);
        }

        for ($round = 0; $round < 2; $round++) {
            $parts = $response->json('candidates.0.content.parts') ?? [];
            $functionCalls = $this->functionCalls($parts);

            if ($functionCalls === []) {
                return $this->textFromParts($parts) ?? ($vietnamese
                    ? "Sorry, I didn't understand that."
                    : 'Sorry, I did not understand that.');
            }

            $responseParts = [];
            foreach ($functionCalls as $functionCall) {
                $dbResult = $this->toolResolver->execute(
                    $functionCall['name'],
                    $functionCall['args'] ?? [],
                );
                $functionResponse = [
                    'name' => $functionCall['name'],
                    'response' => array_merge($dbResult, [
                        'reply_language' => 'en',
                    ]),
                ];
                if (isset($functionCall['id']) && is_string($functionCall['id']) && $functionCall['id'] !== '') {
                    $functionResponse['id'] = $functionCall['id'];
                }
                $responseParts[] = [
                    'functionResponse' => $functionResponse,
                ];
            }

            $contents[] = [
                'role' => 'model',
                'parts' => $this->replayParts($parts),
            ];
            $contents[] = [
                'role' => 'user',
                'parts' => $responseParts,
            ];

            $response = $this->post([
                'system_instruction' => $systemInstruction,
                'contents' => $contents,
                'tools' => $this->toolResolver->getToolsDeclaration(),
            ]);

            if ($response->failed()) {
                return $this->failureMessage($response, 'synthesize', $vietnamese);
            }
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];

        return $this->textFromParts($parts) ?? ($vietnamese
            ? 'Checked produce information.'
            : 'Checked the product information.');
    }

    private function vietnamese(string $text): bool
    {
        return (bool) preg_match('/[àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ]/iu', $text);
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array<int, array<string, mixed>>
     */
    private function contentsFromHistory(array $history): array
    {
        $contents = [];

        foreach (array_slice($history, -12) as $turn) {
            $role = ($turn['role'] ?? '') === 'model' ? 'model' : 'user';
            $text = trim((string) ($turn['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $text]],
            ];
        }

        return $contents;
    }

    /**
     * json_decode turns an empty args object into []. Gemini then rejects the
     * replayed function call because args must be an object.
     *
     * @param  array<int, array<string, mixed>>  $parts
     * @return array<int, array<string, mixed>>
     */
    private function replayParts(array $parts): array
    {
        foreach ($parts as &$part) {
            if (! isset($part['functionCall']) || ! is_array($part['functionCall'])) {
                continue;
            }

            $args = $part['functionCall']['args'] ?? null;
            if ($args === null || $args === []) {
                $part['functionCall']['args'] = new \stdClass;
            }
        }
        unset($part);

        return $parts;
    }

    /**
     * @param  array<int, array<string, mixed>>  $parts
     * @return array<int, array<string, mixed>>
     */
    private function functionCalls(array $parts): array
    {
        $calls = [];

        foreach ($parts as $part) {
            if (isset($part['functionCall']['name'])) {
                $calls[] = $part['functionCall'];
            }
        }

        return $calls;
    }

    /**
     * @param  array<int, array<string, mixed>>  $parts
     */
    private function textFromParts(array $parts): ?string
    {
        $texts = [];
        foreach ($parts as $part) {
            if (isset($part['text']) && is_string($part['text']) && trim($part['text']) !== '') {
                $texts[] = trim($part['text']);
            }
        }

        return $texts === [] ? null : implode("\n", $texts);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function post(array $payload): Response
    {
        $model = config('services.gemini.model', 'gemini-3.5-flash-lite');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";

        $response = null;

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $response = Http::timeout(25)
                ->withHeaders([
                    'x-goog-api-key' => config('services.gemini.api_key'),
                ])
                ->post($url, $payload);

            if ($response->status() !== 503) {
                return $response;
            }

            usleep(500000 * ($attempt + 1));
        }

        return $response;
    }

    private function failureMessage(Response $response, string $step, bool $vietnamese): string
    {
        Log::error("Gemini {$step}", [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

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