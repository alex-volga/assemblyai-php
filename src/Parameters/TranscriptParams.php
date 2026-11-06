<?php

declare(strict_types=1);

namespace AssemblyAI\Parameters;

use AssemblyAI\Enums\PiiPolicy;
use AssemblyAI\Enums\PiiSubstitution;
use AssemblyAI\Enums\SpeechModel;
use AssemblyAI\Enums\SummaryEffort;
use AssemblyAI\Enums\SummaryModel;
use AssemblyAI\Enums\SummaryType;

final class TranscriptParams
{
    /**
     * @param list<string>|null $wordBoost
     * @param list<PiiPolicy|string>|null $redactPiiPolicies
     * @param list<string>|null $keytermsPrompt
     * @param list<string>|null $topics
     * @param array<string, list<string>>|null $customSpelling
     * @param array<string, mixed> $extra
     * @param list<SpeechModel|string>|null $speechModels
     * @param SpeechModel|string|null $speechModel Deprecated: Use $speechModels instead.
     * @param array<string, mixed>|null $speechUnderstanding
     */
    public function __construct(
        public string $audioUrl,
        public SpeechModel|string|null $speechModel = null,
        public ?string $languageCode = null,
        public ?bool $languageDetection = null,
        public ?bool $punctuate = null,
        public ?bool $formatText = null,
        public ?bool $dualChannel = null,
        public ?string $webhookUrl = null,
        public ?string $webhookAuthHeaderName = null,
        public ?string $webhookAuthHeaderValue = null,
        public ?int $audioStartFrom = null,
        public ?int $audioEndAt = null,
        public ?array $wordBoost = null,
        public ?string $boostParam = null,
        public ?bool $filterProfanity = null,
        public ?bool $redactPii = null,
        public ?bool $redactPiiAudio = null,
        public ?array $redactPiiPolicies = null,
        public PiiSubstitution|string|null $redactPiiSub = null,
        public ?bool $speakerLabels = null,
        public ?int $speakersExpected = null,
        public ?bool $contentSafety = null,
        public ?bool $iabCategories = null,
        public ?bool $autoChapters = null,
        public ?bool $sentimentAnalysis = null,
        public ?bool $autoHighlights = null,
        public ?bool $entityDetection = null,
        public ?bool $summarization = null,
        public SummaryModel|string|null $summaryModel = null,
        public SummaryType|string|null $summaryType = null,
        public ?array $customSpelling = null,
        public ?bool $disfluencies = null,
        public ?string $prompt = null,
        public ?array $keytermsPrompt = null,
        public ?bool $customTopics = null,
        public ?array $topics = null,
        public array $extra = [],
        public ?array $speechModels = null,
        public ?array $speechUnderstanding = null,
    ) {
        if ($this->speechModels === null && $this->speechModel !== null) {
            $this->speechModels = [$this->speechModel];
        } elseif ($this->speechModels !== null && $this->speechModel === null && count($this->speechModels) > 0) {
            $this->speechModel = $this->speechModels[0];
        }

        if ($this->speechUnderstanding === null && ($this->autoChapters || $this->summarization)) {
            $this->speechUnderstanding = $this->buildSpeechUnderstandingConfig();
        }
    }

    public static function create(string $audioUrl): self
    {
        return new self(audioUrl: $audioUrl);
    }

    /**
     * @deprecated Use withSpeechModels() instead.
     */
    public function withSpeechModel(SpeechModel|string $speechModel): self
    {
        $this->speechModel = $speechModel;
        $this->speechModels = [$speechModel];
        return $this;
    }

    /**
     * @param array<array-key, SpeechModel|string>|SpeechModel|string $speechModels
     */
    public function withSpeechModels(array|SpeechModel|string $speechModels): self
    {
        $models = is_array($speechModels) ? array_values($speechModels) : [$speechModels];
        $this->speechModels = $models;
        $this->speechModel = $models[0] ?? null;
        return $this;
    }

    public function withLanguageCode(string $code): self
    {
        $this->languageCode = $code;
        return $this;
    }

    public function withLanguageDetection(bool $enabled = true): self
    {
        $this->languageDetection = $enabled;
        return $this;
    }

    public function withSpeakerLabels(bool $enabled = true, ?int $speakersExpected = null): self
    {
        $this->speakerLabels = $enabled;
        $this->speakersExpected = $speakersExpected;
        return $this;
    }

    public function withPunctuate(bool $enabled = true): self
    {
        $this->punctuate = $enabled;
        return $this;
    }

    public function withFormatText(bool $enabled = true): self
    {
        $this->formatText = $enabled;
        return $this;
    }

    public function withDualChannel(bool $enabled = true): self
    {
        $this->dualChannel = $enabled;
        return $this;
    }

    public function withAutoChapters(bool $enabled = true): self
    {
        $this->autoChapters = $enabled;
        if ($enabled) {
            $this->speechUnderstanding ??= [];
            /** @var array<string, mixed> $req */
            $req = $this->speechUnderstanding['request'] ?? [];
            /** @var array<string, mixed> $sum */
            $sum = $req['summarization'] ?? [];
            $sum['summary_type'] ??= 'paragraph';
            $req['summarization'] = $sum;
            $this->speechUnderstanding['request'] = $req;
        }
        return $this;
    }

    public function withSentimentAnalysis(bool $enabled = true): self
    {
        $this->sentimentAnalysis = $enabled;
        return $this;
    }

    public function withEntityDetection(bool $enabled = true): self
    {
        $this->entityDetection = $enabled;
        return $this;
    }

    public function withContentSafety(bool $enabled = true): self
    {
        $this->contentSafety = $enabled;
        return $this;
    }

    public function withIabCategories(bool $enabled = true): self
    {
        $this->iabCategories = $enabled;
        return $this;
    }

    /**
     * @param SummaryType|string $type bullets or paragraph
     * @param SummaryModel|string|null $model Deprecated: AssemblyAI no longer uses summary_model
     * @param SummaryEffort|string|null $effort low or medium
     */
    public function withSummarization(
        SummaryType|string $type = SummaryType::Paragraph,
        SummaryModel|string|null $model = null,
        SummaryEffort|string|null $effort = null,
    ): self {
        $this->summarization = true;
        $this->summaryType = $type;
        $this->summaryModel = $model;

        $typeVal = $type instanceof SummaryType ? $type->value : (string) $type;
        $normalizedType = match ($typeVal) {
            'bullets', 'bullets_verbose' => 'bullets',
            default => 'paragraph',
        };

        $this->speechUnderstanding ??= [];
        /** @var array<string, mixed> $req */
        $req = $this->speechUnderstanding['request'] ?? [];
        $sumConfig = ['summary_type' => $normalizedType];
        if ($effort !== null) {
            $sumConfig['effort'] = $effort instanceof SummaryEffort ? $effort->value : (string) $effort;
        }
        $req['summarization'] = $sumConfig;
        $this->speechUnderstanding['request'] = $req;

        return $this;
    }

    /**
     * @param array<string, mixed> $request
     */
    public function withSpeechUnderstanding(array $request): self
    {
        $this->speechUnderstanding ??= [];
        /** @var array<string, mixed> $req */
        $req = $this->speechUnderstanding['request'] ?? [];
        $this->speechUnderstanding['request'] = array_merge($req, $request);
        return $this;
    }

    /**
     * @param list<PiiPolicy|string>|null $policies
     */
    public function withPiiRedaction(
        ?array $policies = null,
        PiiSubstitution|string $sub = PiiSubstitution::EntityType,
        bool $redactAudio = false,
    ): self {
        $this->redactPii = true;
        $this->redactPiiPolicies = $policies;
        $this->redactPiiSub = $sub;
        $this->redactPiiAudio = $redactAudio;
        return $this;
    }

    /**
     * @param list<string> $words
     */
    public function withWordBoost(array $words, ?string $boostParam = null): self
    {
        $this->wordBoost = $words;
        $this->boostParam = $boostParam;
        return $this;
    }

    public function withWebhook(
        string $webhookUrl,
        ?string $authHeaderName = null,
        ?string $authHeaderValue = null,
    ): self {
        $this->webhookUrl = $webhookUrl;
        $this->webhookAuthHeaderName = $authHeaderName;
        $this->webhookAuthHeaderValue = $authHeaderValue;
        return $this;
    }

    public function withPrompt(string $prompt): self
    {
        $this->prompt = $prompt;
        return $this;
    }

    /**
     * @param list<string> $keyterms
     */
    public function withKeytermsPrompt(array $keyterms): self
    {
        $this->keytermsPrompt = $keyterms;
        return $this;
    }

    public function withExtra(string $key, mixed $value): self
    {
        $this->extra[$key] = $value;
        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildSpeechUnderstandingConfig(): ?array
    {
        if ($this->speechUnderstanding !== null) {
            return $this->speechUnderstanding;
        }

        if ($this->autoChapters || $this->summarization) {
            $summaryType = 'paragraph';
            if ($this->summaryType !== null) {
                $typeVal = $this->summaryType instanceof SummaryType ? $this->summaryType->value : (string) $this->summaryType;
                $summaryType = match ($typeVal) {
                    'bullets', 'bullets_verbose' => 'bullets',
                    default => 'paragraph',
                };
            } elseif ($this->summarization && !$this->autoChapters) {
                $summaryType = 'bullets';
            }

            return [
                'request' => [
                    'summarization' => [
                        'summary_type' => $summaryType,
                    ],
                ],
            ];
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $speechModels = $this->speechModels;
        if ($speechModels === null && $this->speechModel !== null) {
            $speechModels = [$this->speechModel];
        }

        $serializedSpeechModels = $speechModels !== null ? array_map(
            static fn ($m) => $m instanceof SpeechModel ? $m->value : (string) $m,
            $speechModels,
        ) : null;

        $speechUnderstanding = $this->buildSpeechUnderstandingConfig();

        $data = [
            'audio_url' => $this->audioUrl,
            'speech_models' => $serializedSpeechModels,
            'speech_understanding' => $speechUnderstanding,
            'language_code' => $this->languageCode,
            'language_detection' => $this->languageDetection,
            'punctuate' => $this->punctuate,
            'format_text' => $this->formatText,
            'dual_channel' => $this->dualChannel,
            'webhook_url' => $this->webhookUrl,
            'webhook_auth_header_name' => $this->webhookAuthHeaderName,
            'webhook_auth_header_value' => $this->webhookAuthHeaderValue,
            'audio_start_from' => $this->audioStartFrom,
            'audio_end_at' => $this->audioEndAt,
            'word_boost' => $this->wordBoost,
            'boost_param' => $this->boostParam,
            'filter_profanity' => $this->filterProfanity,
            'redact_pii' => $this->redactPii,
            'redact_pii_audio' => $this->redactPiiAudio,
            'redact_pii_policies' => $this->redactPiiPolicies !== null ? array_map(
                static fn ($p) => $p instanceof PiiPolicy ? $p->value : (string) $p,
                $this->redactPiiPolicies,
            ) : null,
            'redact_pii_sub' => $this->redactPiiSub instanceof PiiSubstitution ? $this->redactPiiSub->value : $this->redactPiiSub,
            'speaker_labels' => $this->speakerLabels,
            'speakers_expected' => $this->speakersExpected,
            'content_safety' => $this->contentSafety,
            'iab_categories' => $this->iabCategories,
            'sentiment_analysis' => $this->sentimentAnalysis,
            'auto_highlights' => $this->autoHighlights,
            'entity_detection' => $this->entityDetection,
            'custom_spelling' => $this->customSpelling,
            'disfluencies' => $this->disfluencies,
            'prompt' => $this->prompt,
            'keyterms_prompt' => $this->keytermsPrompt,
            'custom_topics' => $this->customTopics,
            'topics' => $this->topics,
        ];

        // Merge extra parameters and remove nulls
        $merged = array_merge($data, $this->extra);

        if (isset($merged['speech_model'])) {
            if (!isset($merged['speech_models'])) {
                $merged['speech_models'] = [(string) $merged['speech_model']];
            }
            unset($merged['speech_model']);
        }

        if (isset($merged['auto_chapters'])) {
            if ($merged['auto_chapters'] && !isset($merged['speech_understanding']['request']['summarization'])) {
                $merged['speech_understanding']['request']['summarization'] = ['summary_type' => 'paragraph'];
            }
            unset($merged['auto_chapters']);
        }

        if (isset($merged['summarization'])) {
            if ($merged['summarization'] && !isset($merged['speech_understanding']['request']['summarization'])) {
                $type = isset($merged['summary_type']) ? (string) $merged['summary_type'] : 'paragraph';
                $normType = in_array($type, ['bullets', 'bullets_verbose'], true) ? 'bullets' : 'paragraph';
                $merged['speech_understanding']['request']['summarization'] = ['summary_type' => $normType];
            }
            unset($merged['summarization']);
        }
        unset($merged['summary_model'], $merged['summary_type']);

        return array_filter($merged, static fn ($v) => $v !== null);
    }
}
