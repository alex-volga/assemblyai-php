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
     * @param list<array{from: list<string>, to: string}>|array<string, list<string>|string>|null $customSpelling
     * @param array<string, mixed> $extra
     * @param list<SpeechModel|string>|null $speechModels
     * @param SpeechModel|string|null $speechModel Deprecated: Use $speechModels instead.
     * @param array<string, mixed>|null $speechUnderstanding
     * @param list<string>|null $languageCodes
     * @param array<string, mixed>|null $languageDetectionOptions
     * @param array<string, mixed>|null $speakerOptions
     * @param array<string, mixed>|null $redactPiiAudioOptions
     * @param array<string, list<string>>|null $redactStaticEntities
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
        public ?bool $multichannel = null,
        public ?int $contentSafetyConfidence = null,
        public ?string $domain = null,
        public ?array $languageCodes = null,
        public ?float $languageConfidenceThreshold = null,
        public ?array $languageDetectionOptions = null,
        public ?float $speechThreshold = null,
        public ?array $speakerOptions = null,
        public ?string $redactPiiAudioQuality = null,
        public ?array $redactPiiAudioOptions = null,
        public ?bool $redactPiiReturnUnredacted = null,
        public ?array $redactStaticEntities = null,
        public ?string $removeAudioTags = null,
        public ?float $temperature = null,
    ) {
        if ($this->multichannel === null && $this->dualChannel !== null) {
            $this->multichannel = $this->dualChannel;
        } elseif ($this->multichannel !== null && $this->dualChannel === null) {
            $this->dualChannel = $this->multichannel;
        }
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

    /**
     * @param list<string> $codes
     */
    public function withLanguageCodes(array $codes): self
    {
        $this->languageCodes = $codes;
        $this->languageDetection = true;
        return $this;
    }

    public function withLanguageConfidenceThreshold(?float $threshold): self
    {
        $this->languageConfidenceThreshold = $threshold;
        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withLanguageDetectionOptions(array $options): self
    {
        $this->languageDetectionOptions = $options;
        return $this;
    }

    /**
     * @param array<string, mixed>|null $speakerOptions
     */
    public function withSpeakerLabels(
        bool $enabled = true,
        ?int $speakersExpected = null,
        ?array $speakerOptions = null,
    ): self {
        $this->speakerLabels = $enabled;
        $this->speakersExpected = $speakersExpected;
        if ($speakerOptions !== null) {
            $this->speakerOptions = $speakerOptions;
        }
        return $this;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function withSpeakerOptions(array $options): self
    {
        $this->speakerLabels = true;
        $this->speakerOptions = $options;
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

    public function withMultichannel(bool $enabled = true): self
    {
        $this->multichannel = $enabled;
        $this->dualChannel = $enabled;
        return $this;
    }

    /**
     * @deprecated Use withMultichannel() instead.
     */
    public function withDualChannel(bool $enabled = true): self
    {
        return $this->withMultichannel($enabled);
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

    public function withContentSafety(bool $enabled = true, ?int $confidence = null): self
    {
        $this->contentSafety = $enabled;
        $this->contentSafetyConfidence = $confidence;
        return $this;
    }

    public function withDomain(?string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function withSpeechThreshold(?float $threshold): self
    {
        $this->speechThreshold = $threshold;
        return $this;
    }

    public function withDisfluencies(bool $enabled = true): self
    {
        $this->disfluencies = $enabled;
        return $this;
    }

    public function withFilterProfanity(bool $enabled = true): self
    {
        $this->filterProfanity = $enabled;
        return $this;
    }

    public function withTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function withRemoveAudioTags(?string $option = 'all'): self
    {
        $this->removeAudioTags = $option;
        return $this;
    }

    /**
     * @param list<array{from: list<string>, to: string}>|array<string, list<string>|string> $customSpelling
     */
    public function withCustomSpelling(array $customSpelling): self
    {
        $this->customSpelling = $customSpelling;
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
     * @param array<string, mixed>|null $audioOptions
     * @param array<string, list<string>>|null $staticEntities
     */
    public function withPiiRedaction(
        ?array $policies = null,
        PiiSubstitution|string $sub = PiiSubstitution::EntityName,
        bool $redactAudio = false,
        ?string $audioQuality = null,
        ?array $audioOptions = null,
        ?bool $returnUnredacted = null,
        ?array $staticEntities = null,
    ): self {
        $this->redactPii = true;
        $this->redactPiiPolicies = $policies;
        $this->redactPiiSub = $sub;
        $this->redactPiiAudio = $redactAudio;
        if ($audioQuality !== null) {
            $this->redactPiiAudioQuality = $audioQuality;
        }
        if ($audioOptions !== null) {
            $this->redactPiiAudioOptions = $audioOptions;
        }
        if ($returnUnredacted !== null) {
            $this->redactPiiReturnUnredacted = $returnUnredacted;
        }
        if ($staticEntities !== null) {
            $this->redactStaticEntities = $staticEntities;
        }
        return $this;
    }

    /**
     * @param array<string, list<string>> $entities
     */
    public function withStaticEntityRedaction(array $entities): self
    {
        $this->redactPii = true;
        $this->redactStaticEntities = $entities;
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
     * @return list<array{from: list<string>, to: string}>|null
     */
    private function normalizeCustomSpelling(): ?array
    {
        if ($this->customSpelling === null) {
            return null;
        }

        $result = [];
        /** @var mixed $value */
        foreach ($this->customSpelling as $key => $value) {
            if (is_array($value) && isset($value['to'], $value['from'])) {
                /** @var mixed $from */
                $from = $value['from'];
                $fromList = is_array($from)
                    ? array_map(static fn (mixed $item): string => (string) $item, array_values($from))
                    : [(string) $from];

                $result[] = [
                    'from' => $fromList,
                    'to' => (string) $value['to'],
                ];
            } else {
                $fromList = is_array($value)
                    ? array_map(static fn (mixed $item): string => (string) $item, array_values($value))
                    : [(string) $value];

                $result[] = [
                    'from' => $fromList,
                    'to' => (string) $key,
                ];
            }
        }

        return $result;
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

        $multichannel = $this->multichannel ?? $this->dualChannel;

        $customSpelling = $this->normalizeCustomSpelling();

        $data = [
            'audio_url' => $this->audioUrl,
            'speech_models' => $serializedSpeechModels,
            'speech_understanding' => $speechUnderstanding,
            'language_code' => $this->languageCode,
            'language_codes' => $this->languageCodes,
            'language_confidence_threshold' => $this->languageConfidenceThreshold,
            'language_detection' => $this->languageDetection,
            'language_detection_options' => $this->languageDetectionOptions,
            'punctuate' => $this->punctuate,
            'format_text' => $this->formatText,
            'multichannel' => $multichannel,
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
            'redact_pii_audio_quality' => $this->redactPiiAudioQuality,
            'redact_pii_audio_options' => $this->redactPiiAudioOptions,
            'redact_pii_return_unredacted' => $this->redactPiiReturnUnredacted,
            'redact_pii_policies' => $this->redactPiiPolicies !== null ? array_map(
                static function ($p): string {
                    $val = $p instanceof PiiPolicy ? $p->value : (string) $p;
                    return match ($val) {
                        'physical_address' => PiiPolicy::LocationAddress->value,
                        'pin' => PiiPolicy::NumberSequence->value,
                        default => $val,
                    };
                },
                $this->redactPiiPolicies,
            ) : null,
            'redact_pii_sub' => match ($this->redactPiiSub) {
                null => null,
                'entity_type' => PiiSubstitution::EntityName->value,
                default => $this->redactPiiSub instanceof PiiSubstitution ? $this->redactPiiSub->value : $this->redactPiiSub,
            },
            'redact_static_entities' => $this->redactStaticEntities,
            'speaker_labels' => $this->speakerLabels,
            'speakers_expected' => $this->speakersExpected,
            'speaker_options' => $this->speakerOptions,
            'content_safety' => $this->contentSafety,
            'content_safety_confidence' => $this->contentSafetyConfidence,
            'iab_categories' => $this->iabCategories,
            'sentiment_analysis' => $this->sentimentAnalysis,
            'auto_highlights' => $this->autoHighlights,
            'entity_detection' => $this->entityDetection,
            'custom_spelling' => $customSpelling,
            'disfluencies' => $this->disfluencies,
            'domain' => $this->domain,
            'prompt' => $this->prompt,
            'keyterms_prompt' => $this->keytermsPrompt,
            'speech_threshold' => $this->speechThreshold,
            'temperature' => $this->temperature,
            'remove_audio_tags' => $this->removeAudioTags,
            'custom_topics' => $this->customTopics,
            'topics' => $this->topics,
        ];

        // Merge extra parameters and remove nulls
        $merged = array_merge($data, $this->extra);

        if (isset($merged['dual_channel'])) {
            $merged['multichannel'] ??= $merged['dual_channel'];
            unset($merged['dual_channel']);
        }

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
