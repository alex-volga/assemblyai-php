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
}
