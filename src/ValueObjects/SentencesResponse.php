<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class SentencesResponse
{
    /**
     * @param list<Sentence> $sentences
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public array $sentences = [],
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $sentences = [];
        if (isset($data['sentences']) && is_array($data['sentences'])) {
            foreach ($data['sentences'] as $s) {
                if (is_array($s)) {
                    $sentences[] = Sentence::fromArray($s);
                }
            }
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            sentences: $sentences,
            raw: $data,
        );
    }
}
