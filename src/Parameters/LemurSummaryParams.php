<?php

declare(strict_types=1);

namespace AssemblyAI\Parameters;

use AssemblyAI\Enums\LemurModel;

final readonly class LemurSummaryParams
{
    /**
     * @param list<string> $transcriptIds
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public array $transcriptIds,
        public ?string $context = null,
        public ?string $answerFormat = null,
        public ?string $inputText = null,
        public LemurModel|string|null $finalModel = null,
        public ?int $maxOutputSize = null,
        public ?float $temperature = null,
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
            'context' => $this->context,
            'answer_format' => $this->answerFormat,
            'input_text' => $this->inputText,
            'final_model' => $this->finalModel instanceof LemurModel ? $this->finalModel->value : $this->finalModel,
            'max_output_size' => $this->maxOutputSize,
            'temperature' => $this->temperature,
        ];

        return array_filter(array_merge($data, $this->extra), static fn ($v) => $v !== null);
    }
}
