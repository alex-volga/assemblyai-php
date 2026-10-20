<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class LemurResponse
{
    /**
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $requestId,
        public string $response,
        public ?LemurUsage $usage = null,
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $usage = null;
        if (isset($data['usage']) && is_array($data['usage'])) {
            $usage = LemurUsage::fromArray($data['usage']);
        }

        return new self(
            requestId: (string) ($data['request_id'] ?? ''),
            response: (string) ($data['response'] ?? ''),
            usage: $usage,
            raw: $data,
        );
    }
}
