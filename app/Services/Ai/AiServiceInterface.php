<?php

namespace App\Services\Ai;

interface AiServiceInterface
{
    /**
     * @param  array<int, array{role: string, text: string}>  $history
     * @return array{reply: string, suggestions: array<int, string>}
     */
    public function ask(string $userPrompt, array $history = []): array;
}
