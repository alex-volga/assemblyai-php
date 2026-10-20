<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

use AssemblyAI\Enums\TranscriptStatus;

final readonly class TranscriptListItem
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public TranscriptStatus $status,
        public string $audioUrl,
        public ?string $created = null,
        public ?string $completed = null,
        public ?float $audioDuration = null,
        public ?string $error = null,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $statusStr = (string) ($data['status'] ?? 'queued');
        $status = TranscriptStatus::tryFrom($statusStr) ?? TranscriptStatus::Queued;

        return new self(
            id: (string) ($data['id'] ?? ''),
            status: $status,
            audioUrl: (string) ($data['audio_url'] ?? ''),
            created: isset($data['created']) ? (string) $data['created'] : null,
            completed: isset($data['completed']) ? (string) $data['completed'] : null,
            audioDuration: isset($data['audio_duration']) ? (float) $data['audio_duration'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
            raw: $data,
        );
    }
}
