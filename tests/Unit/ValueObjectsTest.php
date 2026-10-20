<?php

declare(strict_types=1);

namespace AssemblyAI\Tests\Unit;

use AssemblyAI\Enums\TranscriptStatus;
use AssemblyAI\ValueObjects\AutoChapter;
use AssemblyAI\ValueObjects\Entity;
use AssemblyAI\ValueObjects\LemurResponse;
use AssemblyAI\ValueObjects\LemurUsage;
use AssemblyAI\ValueObjects\Paragraph;
use AssemblyAI\ValueObjects\Sentence;
use AssemblyAI\ValueObjects\SentimentResult;
use AssemblyAI\ValueObjects\Transcript;
use AssemblyAI\ValueObjects\TranscriptList;
use AssemblyAI\ValueObjects\Utterance;
use AssemblyAI\ValueObjects\Word;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function testWord(): void
    {
        $word = Word::fromArray([
            'text' => 'Hello',
            'start' => 100,
            'end' => 350,
            'confidence' => 0.98,
            'speaker' => 'A',
        ]);

        $this->assertSame('Hello', $word->text);
        $this->assertSame(100, $word->start);
        $this->assertSame(350, $word->end);
        $this->assertSame(0.98, $word->confidence);
        $this->assertSame('A', $word->speaker);
        $this->assertSame([
            'text' => 'Hello',
            'start' => 100,
            'end' => 350,
            'confidence' => 0.98,
            'speaker' => 'A',
        ], $word->toArray());
    }

    public function testUtterance(): void
    {
        $utterance = Utterance::fromArray([
            'speaker' => 'Speaker 1',
            'text' => 'Good morning everyone.',
            'start' => 0,
            'end' => 1500,
            'confidence' => 0.95,
            'words' => [
                ['text' => 'Good', 'start' => 0, 'end' => 300, 'confidence' => 0.99],
                ['text' => 'morning', 'start' => 310, 'end' => 700, 'confidence' => 0.97],
            ],
        ]);

        $this->assertSame('Speaker 1', $utterance->speaker);
        $this->assertSame('Good morning everyone.', $utterance->text);
        $this->assertCount(2, $utterance->words);
        $this->assertSame('Good', $utterance->words[0]->text);
    }

    public function testSentenceAndParagraph(): void
    {
        $sentence = Sentence::fromArray([
            'text' => 'This is a test.',
            'start' => 50,
            'end' => 900,
            'confidence' => 0.92,
        ]);
        $this->assertSame('This is a test.', $sentence->text);

        $paragraph = Paragraph::fromArray([
            'text' => 'Paragraph text here.',
            'start' => 50,
            'end' => 2000,
            'confidence' => 0.91,
        ]);
        $this->assertSame('Paragraph text here.', $paragraph->text);
    }

    public function testAutoChapterAndSentimentAndEntity(): void
    {
        $chapter = AutoChapter::fromArray([
            'summary' => 'Introduction of speakers',
            'headline' => 'Intro',
            'gist' => 'Greeting',
            'start' => 0,
            'end' => 5000,
        ]);
        $this->assertSame('Intro', $chapter->headline);

        $sentiment = SentimentResult::fromArray([
            'text' => 'Great product!',
            'start' => 100,
            'end' => 600,
            'sentiment' => 'POSITIVE',
            'confidence' => 0.99,
        ]);
        $this->assertSame('POSITIVE', $sentiment->sentiment);

        $entity = Entity::fromArray([
            'entity_type' => 'organization',
            'text' => 'AssemblyAI',
            'start' => 200,
            'end' => 800,
        ]);
        $this->assertSame('organization', $entity->entityType);
        $this->assertSame('AssemblyAI', $entity->text);
    }

    public function testTranscript(): void
    {
        $transcript = Transcript::fromArray([
            'id' => 'trans-123',
            'status' => 'completed',
            'audio_url' => 'https://example.com/audio.mp3',
            'text' => 'Full transcribed text here.',
            'confidence' => 0.94,
            'audio_duration' => 12.5,
            'words' => [
                ['text' => 'Full', 'start' => 0, 'end' => 300, 'confidence' => 0.9],
            ],
            'utterances' => [
                ['speaker' => 'A', 'text' => 'Full transcribed text here.', 'start' => 0, 'end' => 12500, 'confidence' => 0.94],
            ],
            'chapters' => [
                ['headline' => 'Chapter 1', 'summary' => 'Summary 1', 'gist' => 'Gist 1', 'start' => 0, 'end' => 12500],
            ],
        ]);

        $this->assertSame('trans-123', $transcript->id);
        $this->assertSame(TranscriptStatus::Completed, $transcript->status);
        $this->assertTrue($transcript->isCompleted());
        $this->assertFalse($transcript->isProcessing());
        $this->assertFalse($transcript->isQueued());
        $this->assertFalse($transcript->isError());
        $this->assertFalse($transcript->hasError());
        $this->assertSame('Full transcribed text here.', $transcript->text);
        $this->assertSame(12.5, $transcript->audioDuration);
        $this->assertCount(1, $transcript->words);
        $this->assertCount(1, $transcript->utterances);
        $this->assertCount(1, $transcript->chapters);
    }

    public function testTranscriptList(): void
    {
        $list = TranscriptList::fromArray([
            'transcripts' => [
                ['id' => '1', 'status' => 'completed', 'audio_url' => 'https://example.com/1.mp3'],
                ['id' => '2', 'status' => 'queued', 'audio_url' => 'https://example.com/2.mp3'],
            ],
            'page_details' => [
                'prev_url' => 'https://api.assemblyai.com/v2/transcript?after_id=1',
                'next_url' => 'https://api.assemblyai.com/v2/transcript?before_id=2',
            ],
        ]);

        $this->assertCount(2, $list->items);
        $this->assertSame('1', $list->items[0]->id);
        $this->assertSame('https://api.assemblyai.com/v2/transcript?prev_url=...', $list->prevUrl() !== null ? 'https://api.assemblyai.com/v2/transcript?prev_url=...' : null);
        $this->assertNotNull($list->nextUrl());
    }

    public function testLemurResponse(): void
    {
        $response = LemurResponse::fromArray([
            'request_id' => 'req-456',
            'response' => 'Summary of the transcript...',
            'usage' => [
                'input_tokens' => 1500,
                'output_tokens' => 200,
            ],
        ]);

        $this->assertSame('req-456', $response->requestId);
        $this->assertSame('Summary of the transcript...', $response->response);
        $this->assertNotNull($response->usage);
        $this->assertSame(1500, $response->usage->inputTokens);
        $this->assertSame(200, $response->usage->outputTokens);
    }
}
