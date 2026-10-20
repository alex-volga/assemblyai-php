<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Feature;

use AssemblyAI\AssemblyAIClient;
use AssemblyAI\Config;
use AssemblyAI\Enums\SubtitleFormat;
use AssemblyAI\Enums\TranscriptStatus;
use AssemblyAI\Exceptions\TimeoutException;
use AssemblyAI\Exceptions\TranscriptionFailedException;
use AssemblyAI\Parameters\TranscriptListParams;
use AssemblyAI\Parameters\TranscriptParams;
use AssemblyAI\Tests\MockHttpClient;
use PHPUnit\Framework\TestCase;

final class TranscriptResourceTest extends TestCase
{
    public function testCreateTranscript(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'status' => 'queued',
            'audio_url' => 'https://example.com/audio.mp3',
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $transcript = $client->transcripts()->create(
            TranscriptParams::create('https://example.com/audio.mp3')
                ->withSpeakerLabels(true)
        );

        $this->assertSame('tx-123', $transcript->id);
        $this->assertSame(TranscriptStatus::Queued, $transcript->status);
        $this->assertTrue($transcript->isQueued());

        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $this->assertSame('POST', $req->getMethod());
        $this->assertSame('https://api.assemblyai.com/v2/transcript', (string) $req->getUri());
    }

    public function testGetTranscript(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'status' => 'completed',
            'audio_url' => 'https://example.com/audio.mp3',
            'text' => 'Hello world transcription',
            'confidence' => 0.99,
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $transcript = $client->transcripts()->get('tx-123');

        $this->assertSame('tx-123', $transcript->id);
        $this->assertTrue($transcript->isCompleted());
        $this->assertSame('Hello world transcription', $transcript->text);
    }

    public function testDeleteTranscript(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $res = $client->transcripts()->delete('tx-123');

        $this->assertSame('tx-123', $res->id);
    }

    public function testListTranscripts(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'transcripts' => [
                ['id' => 'tx-1', 'status' => 'completed', 'audio_url' => 'https://example.com/1.mp3'],
                ['id' => 'tx-2', 'status' => 'queued', 'audio_url' => 'https://example.com/2.mp3'],
            ],
            'page_details' => [
                'next_url' => 'https://api.assemblyai.com/v2/transcript?before_id=tx-2',
            ],
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $list = $client->transcripts()->list(new TranscriptListParams(limit: 20, status: TranscriptStatus::Completed));

        $this->assertCount(2, $list->items);
        $req = $mock->getLastRequest();
        $this->assertNotNull($req);
        $this->assertStringContainsString('limit=20', (string) $req->getUri());
        $this->assertStringContainsString('status=completed', (string) $req->getUri());
    }

    public function testSentencesAndParagraphs(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'sentences' => [
                ['text' => 'Sentence one.', 'start' => 0, 'end' => 1000, 'confidence' => 0.98],
            ],
        ]);
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'paragraphs' => [
                ['text' => 'Paragraph one.', 'start' => 0, 'end' => 3000, 'confidence' => 0.97],
            ],
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $sentences = $client->transcripts()->sentences('tx-123');
        $this->assertCount(1, $sentences->sentences);
        $this->assertSame('Sentence one.', $sentences->sentences[0]->text);

        $paragraphs = $client->transcripts()->paragraphs('tx-123');
        $this->assertCount(1, $paragraphs->paragraphs);
        $this->assertSame('Paragraph one.', $paragraphs->paragraphs[0]->text);
    }

    public function testSubtitlesSrtAndVtt(): void
    {
        $mock = new MockHttpClient();
        $mock->addStringResponse(200, "1\n00:00:01,000 --> 00:00:04,000\nHello world\n");
        $mock->addStringResponse(200, "WEBVTT\n\n1\n00:00:01.000 --> 00:00:04.000\nHello world\n");

        $client = new AssemblyAIClient('test-key', $mock);
        $srt = $client->transcripts()->subtitles('tx-123', SubtitleFormat::Srt, 32);
        $this->assertStringContainsString('00:00:01,000', $srt);

        $vtt = $client->transcripts()->subtitles('tx-123', SubtitleFormat::Vtt);
        $this->assertStringContainsString('WEBVTT', $vtt);
    }

    public function testWordSearch(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'total_count' => 2,
            'matches' => [
                ['text' => 'apple', 'count' => 2, 'timestamps' => [[100, 200], [400, 500]]],
            ],
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $res = $client->transcripts()->wordSearch('tx-123', ['apple']);

        $this->assertSame(2, $res->totalCount);
        $this->assertCount(1, $res->matches);
    }

    public function testRedactedAudio(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'status' => 'redacted_audio_ready',
            'redacted_audio_url' => 'https://cdn.assemblyai.com/redacted/audio.mp3',
        ]);

        $client = new AssemblyAIClient('test-key', $mock);
        $res = $client->transcripts()->redactedAudio('tx-123');

        $this->assertSame('redacted_audio_ready', $res->status);
        $this->assertSame('https://cdn.assemblyai.com/redacted/audio.mp3', $res->redactedAudioUrl);
    }

    public function testWaitUntilReadyPollsUntilCompleted(): void
    {
        $mock = new MockHttpClient();
        // 1st poll: processing
        $mock->addJsonResponse(200, ['id' => 'tx-123', 'status' => 'processing', 'audio_url' => 'url']);
        // 2nd poll: completed
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'status' => 'completed',
            'audio_url' => 'url',
            'text' => 'Finished transcribing.',
        ]);

        $config = new Config(apiKey: 'key', pollInterval: 1, pollTimeout: 5);
        $client = new AssemblyAIClient($config, $mock);

        $pollCount = 0;
        $final = $client->transcripts()->waitUntilReady(
            id: 'tx-123',
            pollInterval: 1,
            timeout: 5,
            onPoll: function () use (&$pollCount) {
                $pollCount++;
            }
        );

        $this->assertTrue($final->isCompleted());
        $this->assertSame('Finished transcribing.', $final->text);
        $this->assertSame(2, $pollCount);
    }

    public function testWaitUntilReadyThrowsWhenStatusIsError(): void
    {
        $mock = new MockHttpClient();
        $mock->addJsonResponse(200, [
            'id' => 'tx-123',
            'status' => 'error',
            'audio_url' => 'url',
            'error' => 'Audio file is corrupted or empty.',
        ]);

        $client = new AssemblyAIClient('key', $mock);

        $this->expectException(TranscriptionFailedException::class);
        $this->expectExceptionMessage('Audio file is corrupted or empty.');

        $client->transcripts()->waitUntilReady('tx-123', pollInterval: 1, timeout: 5);
    }

    public function testWaitUntilReadyThrowsWhenTimeoutReached(): void
    {
        $mock = new MockHttpClient();
        // Keep returning queued
        $mock->addJsonResponse(200, ['id' => 'tx-123', 'status' => 'queued', 'audio_url' => 'url']);
        $mock->addJsonResponse(200, ['id' => 'tx-123', 'status' => 'queued', 'audio_url' => 'url']);
        $mock->addJsonResponse(200, ['id' => 'tx-123', 'status' => 'queued', 'audio_url' => 'url']);

        $client = new AssemblyAIClient('key', $mock);

        $this->expectException(TimeoutException::class);

        $client->transcripts()->waitUntilReady('tx-123', pollInterval: 1, timeout: 1);
    }

    public function testTranscribeConvenienceMethod(): void
    {
        $mock = new MockHttpClient();
        // Upload audio
        $mock->addJsonResponse(200, ['upload_url' => 'https://cdn.assemblyai.com/upload/audio.mp3']);
        // Create transcript
        $mock->addJsonResponse(200, ['id' => 'tx-456', 'status' => 'queued', 'audio_url' => 'https://cdn.assemblyai.com/upload/audio.mp3']);
        // Poll transcript
        $mock->addJsonResponse(200, ['id' => 'tx-456', 'status' => 'completed', 'audio_url' => 'https://cdn.assemblyai.com/upload/audio.mp3', 'text' => 'Quick brown fox']);

        $client = new AssemblyAIClient('key', $mock);
        $transcript = $client->transcribe('binary audio bytes', pollInterval: 1, timeout: 5);

        $this->assertSame('tx-456', $transcript->id);
        $this->assertSame('Quick brown fox', $transcript->text);
        $this->assertCount(3, $mock->requests);
    }
}
