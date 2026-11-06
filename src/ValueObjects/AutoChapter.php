<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class AutoChapter
{
    public function __construct(
        public string $summary,
        public string $headline,
        public string $gist,
        public int $start,
        public int $end,
        public ?string $text = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $text = isset($data['text']) ? (string) $data['text'] : (isset($data['summary']) ? (string) $data['summary'] : null);
        $summary = (string) ($data['summary'] ?? $text ?? '');
        $headline = (string) ($data['headline'] ?? '');
        $gist = (string) ($data['gist'] ?? $headline);

        return new self(
            summary: $summary,
            headline: $headline,
            gist: $gist,
            start: (int) ($data['start'] ?? 0),
            end: (int) ($data['end'] ?? 0),
            text: $text,
        );
    }
}
