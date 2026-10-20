<?php

declare(strict_types=1);

namespace AssemblyAI\ValueObjects;

use AssemblyAI\Enums\TranscriptStatus;

final readonly class Transcript
{
    /**
     * @param list<Word> $words
     * @param list<Utterance> $utterances
     * @param list<AutoChapter> $chapters
     * @param list<SentimentResult> $sentimentAnalysisResults
     * @param list<Entity> $entities
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public string $id,
        public TranscriptStatus $status,
        public string $audioUrl,
        public ?string $text = null,
        public array $words = [],
        public array $utterances = [],
        public array $chapters = [],
        public array $sentimentAnalysisResults = [],
        public array $entities = [],
        public ?float $confidence = null,
        public ?float $audioDuration = null,
        public ?string $error = null,
        public ?string $summary = null,
        public ?string $languageCode = null,
        public ?string $speechModel = null,
        public ?bool $punctuate = null,
        public ?bool $formatText = null,
        public ?bool $dualChannel = null,
        public ?string $webhookUrl = null,
        public ?int $webhookStatusCode = null,
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

        $words = [];
        if (isset($data['words']) && is_array($data['words'])) {
            foreach ($data['words'] as $w) {
                if (is_array($w)) {
                    $words[] = Word::fromArray($w);
                }
            }
        }

        $utterances = [];
        if (isset($data['utterances']) && is_array($data['utterances'])) {
            foreach ($data['utterances'] as $u) {
                if (is_array($u)) {
                    $utterances[] = Utterance::fromArray($u);
                }
            }
        }

        $chapters = [];
        if (isset($data['chapters']) && is_array($data['chapters'])) {
            foreach ($data['chapters'] as $c) {
                if (is_array($c)) {
                    $chapters[] = AutoChapter::fromArray($c);
                }
            }
        }

        $sentiments = [];
        if (isset($data['sentiment_analysis_results']) && is_array($data['sentiment_analysis_results'])) {
            foreach ($data['sentiment_analysis_results'] as $s) {
                if (is_array($s)) {
                    $sentiments[] = SentimentResult::fromArray($s);
                }
            }
        }

        $entities = [];
        if (isset($data['entities']) && is_array($data['entities'])) {
            foreach ($data['entities'] as $e) {
                if (is_array($e)) {
                    $entities[] = Entity::fromArray($e);
                }
            }
        }

        return new self(
            id: (string) ($data['id'] ?? ''),
            status: $status,
            audioUrl: (string) ($data['audio_url'] ?? ''),
            text: isset($data['text']) ? (string) $data['text'] : null,
            words: $words,
            utterances: $utterances,
            chapters: $chapters,
            sentimentAnalysisResults: $sentiments,
            entities: $entities,
            confidence: isset($data['confidence']) ? (float) $data['confidence'] : null,
            audioDuration: isset($data['audio_duration']) ? (float) $data['audio_duration'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
            summary: isset($data['summary']) ? (string) $data['summary'] : null,
            languageCode: isset($data['language_code']) ? (string) $data['language_code'] : null,
            speechModel: isset($data['speech_model']) ? (string) $data['speech_model'] : null,
            punctuate: isset($data['punctuate']) ? (bool) $data['punctuate'] : null,
            formatText: isset($data['format_text']) ? (bool) $data['format_text'] : null,
            dualChannel: isset($data['dual_channel']) ? (bool) $data['dual_channel'] : null,
            webhookUrl: isset($data['webhook_url']) ? (string) $data['webhook_url'] : null,
            webhookStatusCode: isset($data['webhook_status_code']) ? (int) $data['webhook_status_code'] : null,
            raw: $data,
        );
    }

    public function isCompleted(): bool
    {
        return $this->status->isCompleted();
    }

    public function isProcessing(): bool
    {
        return $this->status->isProcessing();
    }

    public function isQueued(): bool
    {
        return $this->status->isQueued();
    }

    public function isError(): bool
    {
        return $this->status->isError();
    }

    public function hasError(): bool
    {
        return $this->isError() || $this->error !== null;
    }
}
