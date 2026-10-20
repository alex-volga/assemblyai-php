<?php

declare(strict_types=1);

namespace AssemblyAI\Parameters;

use AssemblyAI\Enums\LemurModel;

final readonly class LemurTaskParams
{
    /**
     * @param list<string> $transcriptIds
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public array $transcriptIds,
        public string $prompt,
        public ?string $inputText = null,
        public LemurModel|string|null $finalModel = null,
        public ?int $maxOutputSize = null,
        public ?float $temperature = null,
        public ?string $context = null,
        public array $extra = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'transcript_ids' => $this->transcriptIds,
            'prompt' => $this->prompt,
            'input_text' => $this->inputText,
            'final_model' => $this->finalModel instanceof LemurModel ? $this->finalModel->value : $this->finalModel,
            'max_output_size' => $this->maxOutputSize,
            'temperature' => $this->temperature,
            'context' => $this->context,
        ];

        return array_filter(array_merge($data, $this->extra), static fn ($v) => $v !== null);
    }
}
