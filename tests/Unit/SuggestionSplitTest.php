<?php

namespace Tests\Unit;

use App\Services\Ai\GeminiService;
use PHPUnit\Framework\TestCase;

class SuggestionSplitTest extends TestCase
{
    public function test_suggestions_block_is_removed_from_the_visible_reply(): void
    {
        $result = GeminiService::splitSuggestions(<<<'TXT'
Guava from Riverside Orchard is in stock. Want to order it?

SUGGESTIONS:
- Order guava from Riverside Orchard?
- What is the price of king orange?
TXT);

        $this->assertSame(
            'Guava from Riverside Orchard is in stock. Want to order it?',
            $result['reply'],
        );
        $this->assertSame([
            'Order guava from Riverside Orchard?',
            'What is the price of king orange?',
        ], $result['suggestions']);
    }

    public function test_suggestions_are_capped_at_three(): void
    {
        $result = GeminiService::splitSuggestions(<<<'TXT'
King orange is available.

SUGGESTIONS:
- Order king orange from Riverside Orchard?
- What is the price of guava?
- Is cherry tomato in stock?
- Show me another farm
TXT);

        $this->assertSame([
            'Order king orange from Riverside Orchard?',
            'What is the price of guava?',
            'Is cherry tomato in stock?',
        ], $result['suggestions']);
        $this->assertStringNotContainsString('SUGGESTIONS:', $result['reply']);
    }

    public function test_under_development_reply_returns_no_suggestions(): void
    {
        $result = GeminiService::splitSuggestions(<<<'TXT'
This feature is under development.
SUGGESTIONS:
- Order guava from Riverside Orchard?
TXT);

        $this->assertSame('This feature is under development.', $result['reply']);
        $this->assertSame([], $result['suggestions']);
    }

    public function test_reply_without_a_block_keeps_the_full_text(): void
    {
        $result = GeminiService::splitSuggestions('Guava costs $1.00.');

        $this->assertSame('Guava costs $1.00.', $result['reply']);
        $this->assertSame([], $result['suggestions']);
    }
}
