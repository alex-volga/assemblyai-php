<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

final readonly class TranscriptList
{
    /**
     * @param list<TranscriptListItem> $items
     * @param array<string, mixed> $pageDetails
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public array $items = [],
        public array $pageDetails = [],
        public array $raw = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $items = [];
        $transcripts = $data['transcripts'] ?? [];
        if (is_array($transcripts)) {
            foreach ($transcripts as $item) {
                if (is_array($item)) {
                    $items[] = TranscriptListItem::fromArray($item);
                }
            }
        }

        $pageDetails = isset($data['page_details']) && is_array($data['page_details'])
            ? $data['page_details']
            : [];

        return new self(
            items: $items,
            pageDetails: $pageDetails,
            raw: $data,
        );
    }

    public function prevUrl(): ?string
    {
        return isset($this->pageDetails['prev_url']) ? (string) $this->pageDetails['prev_url'] : null;
    }

    public function nextUrl(): ?string
    {
        return isset($this->pageDetails['next_url']) ? (string) $this->pageDetails['next_url'] : null;
    }
}
