<?php

declare(strict_types=1);

namespace AssemblyAI\Parameters;

use AssemblyAI\Enums\TranscriptStatus;

final readonly class TranscriptListParams
{
    public function __construct(
        public ?int $limit = null,
        public ?TranscriptStatus $status = null,
        public ?string $createdOn = null,
        public ?string $beforeId = null,
        public ?string $afterId = null,
        public ?bool $throttledOnly = null,
    ) {
    }

    /**
     * @return array<string, scalar>
     */
    public function toQueryParams(): array
    {
        $params = [
            'limit' => $this->limit,
            'status' => $this->status?->value,
            'created_on' => $this->createdOn,
            'before_id' => $this->beforeId,
            'after_id' => $this->afterId,
            'throttled_only' => $this->throttledOnly !== null ? ($this->throttledOnly ? 'true' : 'false') : null,
        ];

        return array_filter($params, static fn ($v) => $v !== null);
    }
}
