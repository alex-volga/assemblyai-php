<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class LemurUsage
{
    public function __construct(
        public int $inputTokens,
        public int $outputTokens,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            inputTokens: (int) ($data['input_tokens'] ?? 0),
            outputTokens: (int) ($data['output_tokens'] ?? $data['output_token_count'] ?? 0),
        );
    }
}
