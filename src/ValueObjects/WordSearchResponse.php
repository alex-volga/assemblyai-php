<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class WordSearchResponse
{
    /**
     * @param list<array<string, mixed>> $matches
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public int $totalCount,
        public array $matches = [],
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $matches = [];
        if (isset($data['matches']) && is_array($data['matches'])) {
            foreach ($data['matches'] as $match) {
                if (is_array($match)) {
                    /** @var array<string, mixed> $match */
                    $matches[] = $match;
                }
            }
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            totalCount: (int) ($data['total_count'] ?? 0),
            matches: $matches,
            raw: $data,
        );
    }
}
