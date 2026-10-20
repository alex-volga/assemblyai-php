<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class SentimentResult
{
    public function __construct(
        public string $text,
        public int $start,
        public int $end,
        public string $sentiment,
        public float $confidence,
        public ?string $speaker = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: (string) ($data['text'] ?? ''),
            start: (int) ($data['start'] ?? 0),
            end: (int) ($data['end'] ?? 0),
            sentiment: (string) ($data['sentiment'] ?? ''),
            confidence: (float) ($data['confidence'] ?? 0.0),
            speaker: isset($data['speaker']) ? (string) $data['speaker'] : null,
        );
    }
}
