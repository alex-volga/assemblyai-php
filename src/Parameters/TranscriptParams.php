<?php

declare(strict_types=1);

namespace AssemblyAI\Parameters;

use AssemblyAI\Enums\PiiPolicy;
use AssemblyAI\Enums\PiiSubstitution;
use AssemblyAI\Enums\SpeechModel;
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
    ) {
    }

    public static function create(string $audioUrl): self
    {
        return new self(audioUrl: $audioUrl);
    }

    public function withSpeechModel(SpeechModel|string $speechModel): self
    {
        $this->speechModel = $speechModel;
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

    public function withSummarization(
        SummaryType|string $type = SummaryType::Paragraph,
        SummaryModel|string $model = SummaryModel::Informative,
    ): self {
        $this->summarization = true;
        $this->summaryType = $type;
        $this->summaryModel = $model;
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'audio_url' => $this->audioUrl,
            'speech_model' => $this->speechModel instanceof SpeechModel ? $this->speechModel->value : $this->speechModel,
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
            'auto_chapters' => $this->autoChapters,
            'sentiment_analysis' => $this->sentimentAnalysis,
            'auto_highlights' => $this->autoHighlights,
            'entity_detection' => $this->entityDetection,
            'summarization' => $this->summarization,
            'summary_model' => $this->summaryModel instanceof SummaryModel ? $this->summaryModel->value : $this->summaryModel,
            'summary_type' => $this->summaryType instanceof SummaryType ? $this->summaryType->value : $this->summaryType,
            'custom_spelling' => $this->customSpelling,
            'disfluencies' => $this->disfluencies,
            'prompt' => $this->prompt,
            'keyterms_prompt' => $this->keytermsPrompt,
            'custom_topics' => $this->customTopics,
            'topics' => $this->topics,
        ];

        // Merge extra parameters and remove nulls
        $merged = array_merge($data, $this->extra);

        return array_filter($merged, static fn ($v) => $v !== null);
    }
}
