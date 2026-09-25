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
        $apiKey = config('services.gemini.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            return 'Chưa cấu hình GEMINI_API_KEY.';
        }

        $systemInstruction = [
            'parts' => [[
                'text' => 'Bạn là trợ lý chợ nông sản HarvestHub. Trả lời tiếng Việt, ngắn và đúng dữ liệu. Khi người dùng hỏi về sản phẩm, giá, tồn kho, danh mục hoặc trang trại, bắt buộc gọi searchProducts trước. Chỉ nêu sản phẩm có trong kết quả công cụ, không bịa thêm. Nếu không tìm thấy thì nói rõ là không có.',
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
            return $this->failureMessage($response, 'lượt hỏi');
        }

        for ($round = 0; $round < 2; $round++) {
            $parts = $response->json('candidates.0.content.parts') ?? [];
            $functionCall = $this->functionCall($parts);

            if ($functionCall === null) {
                return $this->textFromParts($parts) ?? 'Rất tiếc, tôi chưa hiểu ý của bạn.';
            }

            $dbResult = $this->toolResolver->execute(
                $functionCall['name'],
                $functionCall['args'] ?? [],
            );

            $contents[] = [
                'role' => 'model',
                'parts' => $parts,
            ];
            $contents[] = [
                'role' => 'user',
                'parts' => [[
                    'functionResponse' => [
                        'name' => $functionCall['name'],
                        'response' => $dbResult,
                    ],
                ]],
            ];

            $response = $this->post([
                'system_instruction' => $systemInstruction,
                'contents' => $contents,
                'tools' => $this->toolResolver->getToolsDeclaration(),
            ]);

            if ($response->failed()) {
                return $this->failureMessage($response, 'lượt tổng hợp');
            }
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];

        return $this->textFromParts($parts) ?? 'Đã kiểm tra thông tin nông sản.';
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
     * @return array<string, mixed>|null
     */
    private function functionCall(array $parts): ?array
    {
        foreach ($parts as $part) {
            if (isset($part['functionCall']['name'])) {
                return $part['functionCall'];
            }
        }

        return null;
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

    private function failureMessage(Response $response, string $step): string
    {
        Log::error("Gemini {$step}", [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (in_array($response->status(), [429, 503], true)) {
            return 'Trợ lý AI đang quá tải, vui lòng thử lại sau một lát.';
        }

        return 'Xin lỗi, hiện tại không thể kết nối tới trợ lý AI.';
    }
}
