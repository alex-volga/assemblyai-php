<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class Entity
{
    public function __construct(
        public string $entityType,
        public string $text,
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
            entityType: (string) ($data['entity_type'] ?? ''),
            text: (string) ($data['text'] ?? ''),
            start: (int) ($data['start'] ?? 0),
            end: (int) ($data['end'] ?? 0),
        );
    }
}
