<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class ParagraphsResponse
{
    /**
     * @param list<Paragraph> $paragraphs
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public array $paragraphs = [],
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $paragraphs = [];
        if (isset($data['paragraphs']) && is_array($data['paragraphs'])) {
            foreach ($data['paragraphs'] as $p) {
                if (is_array($p)) {
                    $paragraphs[] = Paragraph::fromArray($p);
                }
            }
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            paragraphs: $paragraphs,
            raw: $data,
        );
    }
}
