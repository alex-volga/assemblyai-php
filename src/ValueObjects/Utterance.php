<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class Utterance
{
    /**
     * @param list<Word> $words
     */
    public function __construct(
        public string $speaker,
        public string $text,
        public int $start,
        public int $end,
        public float $confidence,
        public array $words = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $words = [];
        if (isset($data['words']) && is_array($data['words'])) {
            foreach ($data['words'] as $wordData) {
                if (is_array($wordData)) {
                    $words[] = Word::fromArray($wordData);
                }
            }
        }

        return new self(
            speaker: (string) ($data['speaker'] ?? ''),
            text: (string) ($data['text'] ?? ''),
            start: (int) ($data['start'] ?? 0),
            end: (int) ($data['end'] ?? 0),
            confidence: (float) ($data['confidence'] ?? 0.0),
            words: $words,
        );
    }
}
