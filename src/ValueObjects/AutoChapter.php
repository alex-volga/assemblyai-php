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
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            summary: (string) ($data['summary'] ?? ''),
            headline: (string) ($data['headline'] ?? ''),
            gist: (string) ($data['gist'] ?? ''),
            start: (int) ($data['start'] ?? 0),
            end: (int) ($data['end'] ?? 0),
        );
    }
}
