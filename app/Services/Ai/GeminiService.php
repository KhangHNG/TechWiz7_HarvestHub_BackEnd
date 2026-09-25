<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    public function __construct(private AiToolResolver $toolResolver) {}

    /**
     * Lịch sử chỉ dùng cho request này. Không ghi database.
     *
     * @param  array<int, array{role: string, text: string}>  $history
     */
    public function ask(string $userPrompt, array $history = []): string
    {
        $vietnamese = $this->vietnamese($userPrompt);
        $apiKey = config('services.gemini.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return $vietnamese
                ? 'Chưa cấu hình GEMINI_API_KEY.'
                : 'GEMINI_API_KEY is not configured.';
        }

        $systemInstruction = [
            'parts' => [[
                'text' => 'You are the HarvestHub farm-market assistant. Reply briefly in the language of the current user message. Only answer about products, price, stock, farms, markets, addresses, and pickup hours that come from tool results. For price or stock, call get_product_stock. For market hours, pickup time, or market address, call get_pickup_slots. Do not use general knowledge. Catalog names are Vietnamese. Before calling a tool, pass the Vietnamese catalog name (water spinach = rau muống, cherry tomato = cà chua bi). If the first call returns not_found, call once more with another Vietnamese name. Do not invent products. Keep stored product names, farm names, addresses, hours, and prices unchanged. The tool result includes reply_language. Write the sentence in that language even when product names are Vietnamese. If the result is not_found, say so in reply_language. If the question is outside this scope, such as weather, recipes, news, or small talk, do not call any tool. Reply with exactly "Hệ thống đang trong quá trình phát triển." when reply_language would be vi, or exactly "The system is still under development." otherwise.',
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
                    ? 'Rất tiếc, tôi chưa hiểu ý của bạn.'
                    : 'Sorry, I did not understand that.');
            }

            $responseParts = [];
            foreach ($functionCalls as $functionCall) {
                $dbResult = $this->toolResolver->execute(
                    $functionCall['name'],
                    $functionCall['args'] ?? [],
                );
                $responseParts[] = [
                    'functionResponse' => [
                        'name' => $functionCall['name'],
                        'response' => array_merge($dbResult, [
                            'reply_language' => $vietnamese ? 'vi' : 'en',
                        ]),
                    ],
                ];
            }

            $contents[] = [
                'role' => 'model',
                'parts' => $parts,
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
            ? 'Đã kiểm tra thông tin nông sản.'
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
                ? 'Trợ lý AI đang quá tải, vui lòng thử lại sau một lát.'
                : 'The assistant is busy. Please try again in a moment.';
        }

        return $vietnamese
            ? 'Xin lỗi, hiện tại không thể kết nối tới trợ lý AI.'
            : 'Sorry, the assistant is unavailable right now.';
    }
}