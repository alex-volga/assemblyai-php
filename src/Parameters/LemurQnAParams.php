<?php

declare(strict_types=1);

namespace AssemblyAI\Parameters;

use AssemblyAI\Enums\LemurModel;

final readonly class LemurQnAParams
{
    /**
     * @param list<string> $transcriptIds
     * @param list<array{question: string, answer_format?: string, options?: list<string>}> $questions
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public array $transcriptIds,
        public array $questions,
        public ?string $context = null,
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
            'questions' => $this->questions,
            'context' => $this->context,
            'input_text' => $this->inputText,
            'final_model' => $this->finalModel instanceof LemurModel ? $this->finalModel->value : $this->finalModel,
            'max_output_size' => $this->maxOutputSize,
            'temperature' => $this->temperature,
        ];

        return array_filter(array_merge($data, $this->extra), static fn ($v) => $v !== null);
    }
}
