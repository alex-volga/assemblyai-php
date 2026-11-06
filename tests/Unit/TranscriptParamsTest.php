<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Unit;

use AssemblyAI\Enums\PiiPolicy;
use AssemblyAI\Enums\PiiSubstitution;
use AssemblyAI\Enums\SpeechModel;
use AssemblyAI\Enums\SummaryEffort;
use AssemblyAI\Enums\SummaryModel;
use AssemblyAI\Enums\SummaryType;
use AssemblyAI\Parameters\TranscriptParams;
use PHPUnit\Framework\TestCase;

final class TranscriptParamsTest extends TestCase
{
    public function testCreateAndToArray(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withSpeechModel(SpeechModel::Universal2)
            ->withSpeakerLabels(true, 2)
            ->withLanguageCode('en_us')
            ->withPunctuate(true)
            ->withFormatText(true)
            ->withAutoChapters(true)
            ->withSentimentAnalysis(true)
            ->withEntityDetection(true)
            ->withSummarization(SummaryType::Bullets, SummaryModel::Informative)
            ->withPiiRedaction([PiiPolicy::EmailAddress, PiiPolicy::PhoneNumber], PiiSubstitution::Hash, true)
            ->withWordBoost(['AssemblyAI', 'API'], 'high')
            ->withWebhook('https://webhook.site/test', 'X-Secret', '12345')
            ->withPrompt('Technical podcast about artificial intelligence')
            ->withKeytermsPrompt(['machine learning', 'deep learning'])
            ->withExtra('custom_feature', true);

        $array = $params->toArray();

        $this->assertSame('https://example.com/audio.mp3', $array['audio_url']);
        $this->assertSame(['universal-2'], $array['speech_models']);
        $this->assertArrayNotHasKey('speech_model', $array);
        $this->assertTrue($array['speaker_labels']);
        $this->assertSame(2, $array['speakers_expected']);
        $this->assertSame('en_us', $array['language_code']);
        $this->assertTrue($array['punctuate']);
        $this->assertTrue($array['format_text']);
        $this->assertSame([
            'request' => [
                'summarization' => [
                    'summary_type' => 'bullets',
                ],
            ],
        ], $array['speech_understanding']);
        $this->assertArrayNotHasKey('auto_chapters', $array);
        $this->assertArrayNotHasKey('summarization', $array);
        $this->assertArrayNotHasKey('summary_type', $array);
        $this->assertArrayNotHasKey('summary_model', $array);
        $this->assertTrue($array['sentiment_analysis']);
        $this->assertTrue($array['entity_detection']);
        $this->assertTrue($array['redact_pii']);
        $this->assertSame(['email_address', 'phone_number'], $array['redact_pii_policies']);
        $this->assertSame('hash', $array['redact_pii_sub']);
        $this->assertTrue($array['redact_pii_audio']);
        $this->assertSame(['AssemblyAI', 'API'], $array['word_boost']);
        $this->assertSame('high', $array['boost_param']);
        $this->assertSame('https://webhook.site/test', $array['webhook_url']);
        $this->assertSame('X-Secret', $array['webhook_auth_header_name']);
        $this->assertSame('12345', $array['webhook_auth_header_value']);
        $this->assertSame('Technical podcast about artificial intelligence', $array['prompt']);
        $this->assertSame(['machine learning', 'deep learning'], $array['keyterms_prompt']);
        $this->assertTrue($array['custom_feature']);
    }

    public function testWithSpeechModelsArray(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withSpeechModels([SpeechModel::Universal35Pro, SpeechModel::Universal2]);

        $array = $params->toArray();

        $this->assertSame(['universal-3-5-pro', 'universal-2'], $array['speech_models']);
        $this->assertSame(SpeechModel::Universal35Pro, $params->speechModel);
        $this->assertSame([SpeechModel::Universal35Pro, SpeechModel::Universal2], $params->speechModels);
    }

    public function testLegacySpeechModelInExtraIsConverted(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withExtra('speech_model', 'universal-2');

        $array = $params->toArray();

        $this->assertSame(['universal-2'], $array['speech_models']);
        $this->assertArrayNotHasKey('speech_model', $array);
    }

    public function testAutoChaptersConvertsToSpeechUnderstanding(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withAutoChapters(true);

        $array = $params->toArray();

        $this->assertSame([
            'request' => [
                'summarization' => [
                    'summary_type' => 'paragraph',
                ],
            ],
        ], $array['speech_understanding']);
        $this->assertArrayNotHasKey('auto_chapters', $array);
    }

    public function testSummarizationWithEffort(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withSummarization(SummaryType::Bullets, effort: SummaryEffort::Medium);

        $array = $params->toArray();

        $this->assertSame([
            'request' => [
                'summarization' => [
                    'summary_type' => 'bullets',
                    'effort' => 'medium',
                ],
            ],
        ], $array['speech_understanding']);
    }

    public function testLegacyAutoChaptersAndSummarizationInExtra(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withExtra('auto_chapters', true)
            ->withExtra('summary_model', 'informative');

        $array = $params->toArray();

        $this->assertSame([
            'request' => [
                'summarization' => [
                    'summary_type' => 'paragraph',
                ],
            ],
        ], $array['speech_understanding']);
        $this->assertArrayNotHasKey('auto_chapters', $array);
        $this->assertArrayNotHasKey('summary_model', $array);
    }

    public function testPiiSubstitutionValuesAndBackwardCompatibility(): void
    {
        $this->assertSame('entity_name', PiiSubstitution::EntityName->value);
        $this->assertSame('hash', PiiSubstitution::Hash->value);
        $this->assertSame(PiiSubstitution::EntityName, PiiSubstitution::EntityType);

        $paramsDefault = TranscriptParams::create('https://example.com/audio.mp3')
            ->withPiiRedaction();
        $this->assertSame('entity_name', $paramsDefault->toArray()['redact_pii_sub']);

        $paramsLegacyString = TranscriptParams::create('https://example.com/audio.mp3')
            ->withPiiRedaction(sub: 'entity_type');
        $this->assertSame('entity_name', $paramsLegacyString->toArray()['redact_pii_sub']);

        $paramsEntityName = TranscriptParams::create('https://example.com/audio.mp3')
            ->withPiiRedaction(sub: PiiSubstitution::EntityName);
        $this->assertSame('entity_name', $paramsEntityName->toArray()['redact_pii_sub']);
    }

    public function testSubmitParametersAccordingToApiReference(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withMultichannel(true)
            ->withContentSafety(true, 75)
            ->withDomain('medical-v1')
            ->withSpeechThreshold(0.2)
            ->withDisfluencies(true)
            ->withFilterProfanity(true)
            ->withTemperature(0.5)
            ->withRemoveAudioTags('speaker')
            ->withLanguageCodes(['en', 'es'])
            ->withLanguageConfidenceThreshold(0.85)
            ->withLanguageDetectionOptions([
                'fallback_language' => 'en',
                'on_no_speech_detected' => 'fallback',
            ])
            ->withSpeakerLabels(true, speakerOptions: [
                'min_speakers_expected' => 2,
                'max_speakers_expected' => 4,
                'include_speaker_confidence' => true,
            ])
            ->withCustomSpelling([
                ['from' => ['dicarlo'], 'to' => 'Decarlo'],
            ])
            ->withPiiRedaction(
                policies: [PiiPolicy::PhysicalAddress, PiiPolicy::Pin, PiiPolicy::MedicalCondition],
                sub: PiiSubstitution::EntityName,
                redactAudio: true,
                audioQuality: 'wav',
                audioOptions: ['override_audio_redaction_method' => 'silence'],
                returnUnredacted: true,
                staticEntities: ['INTERNAL_TOOL' => ['Bearclaw', 'Cubclaw']],
            );

        $array = $params->toArray();

        $this->assertTrue($array['multichannel']);
        $this->assertArrayNotHasKey('dual_channel', $array);
        $this->assertTrue($array['content_safety']);
        $this->assertSame(75, $array['content_safety_confidence']);
        $this->assertSame('medical-v1', $array['domain']);
        $this->assertSame(0.2, $array['speech_threshold']);
        $this->assertTrue($array['disfluencies']);
        $this->assertTrue($array['filter_profanity']);
        $this->assertSame(0.5, $array['temperature']);
        $this->assertSame('speaker', $array['remove_audio_tags']);
        $this->assertSame(['en', 'es'], $array['language_codes']);
        $this->assertSame(0.85, $array['language_confidence_threshold']);
        $this->assertSame('fallback', $array['language_detection_options']['on_no_speech_detected']);
        $this->assertSame(2, $array['speaker_options']['min_speakers_expected']);
        $this->assertSame([
            ['from' => ['dicarlo'], 'to' => 'Decarlo'],
        ], $array['custom_spelling']);

        // Check PiiPolicy backwards-compatible normalization
        $this->assertSame(['location_address', 'number_sequence', 'medical_condition'], $array['redact_pii_policies']);
        $this->assertSame('entity_name', $array['redact_pii_sub']);
        $this->assertTrue($array['redact_pii_audio']);
        $this->assertSame('wav', $array['redact_pii_audio_quality']);
        $this->assertSame(['override_audio_redaction_method' => 'silence'], $array['redact_pii_audio_options']);
        $this->assertTrue($array['redact_pii_return_unredacted']);
        $this->assertSame(['INTERNAL_TOOL' => ['Bearclaw', 'Cubclaw']], $array['redact_static_entities']);
    }

    public function testDualChannelMapsToMultichannel(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withDualChannel(true);

        $array = $params->toArray();
        $this->assertTrue($array['multichannel']);
        $this->assertArrayNotHasKey('dual_channel', $array);
    }

    public function testCustomSpellingAssociativeNormalization(): void
    {
        $params = TranscriptParams::create('https://example.com/audio.mp3')
            ->withCustomSpelling([
                'Decarlo' => ['dicarlo'],
                'AssemblyAI' => 'assembly ai',
            ]);

        $array = $params->toArray();
        $this->assertSame([
            ['from' => ['dicarlo'], 'to' => 'Decarlo'],
            ['from' => ['assembly ai'], 'to' => 'AssemblyAI'],
        ], $array['custom_spelling']);
    }
}
