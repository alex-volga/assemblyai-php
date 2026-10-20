<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class Word
{
    public function __construct(
        public string $text,
        public int $start,
        public int $end,
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
            confidence: (float) ($data['confidence'] ?? 0.0),
            speaker: isset($data['speaker']) ? (string) $data['speaker'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'text' => $this->text,
            'start' => $this->start,
            'end' => $this->end,
            'confidence' => $this->confidence,
            'speaker' => $this->speaker,
        ], static fn ($value) => $value !== null);
    }
}
