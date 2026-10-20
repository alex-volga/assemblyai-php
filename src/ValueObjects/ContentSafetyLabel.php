<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class ContentSafetyLabel
{
    public function __construct(
        public string $label,
        public float $confidence,
        public ?float $severity = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            label: (string) ($data['label'] ?? ''),
            confidence: (float) ($data['confidence'] ?? 0.0),
            severity: isset($data['severity']) ? (float) $data['severity'] : null,
        );
    }
}
