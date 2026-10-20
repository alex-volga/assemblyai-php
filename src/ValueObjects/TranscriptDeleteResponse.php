<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class TranscriptDeleteResponse
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            raw: $data,
        );
    }
}
