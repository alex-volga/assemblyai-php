<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class RedactedAudioResponse
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $status,
        public ?string $redactedAudioUrl = null,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? ''),
            redactedAudioUrl: isset($data['redacted_audio_url']) ? (string) $data['redacted_audio_url'] : null,
            raw: $data,
        );
    }
}
