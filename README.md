# AssemblyAI PHP SDK

[![CI](https://github.com/alex-volga/assemblyai-php/actions/workflows/ci.yml/badge.svg)](https://github.com/alex-volga/assemblyai-php/actions/workflows/ci.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/alex-volga/assemblyai-php.svg)](https://packagist.org/packages/alex-volga/assemblyai-php)
[![Total Downloads](https://img.shields.io/packagist/dt/alex-volga/assemblyai-php.svg)](https://packagist.org/packages/alex-volga/assemblyai-php)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.3-8892BF.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A modern, strongly typed PHP SDK for the [AssemblyAI](https://www.assemblyai.com) Speech-to-Text and Audio Intelligence API.

Designed from the ground up following modern PHP 8.3+ best practices:
- **PHP 8.3+**: `readonly` classes and DTOs, typed class constants, Backed Enums, pattern matching (`match`), and strict typing (`declare(strict_types=1)`).
- **PSR Standards Compliant**: PSR-7 (HTTP Messages), PSR-17 (HTTP Factories), and PSR-18 (HTTP Client). Decoupled from HTTP implementations — works out-of-the-box with Guzzle, or inject any PSR-18 client (e.g. Symfony HTTP Client).
- **Full API Coverage**:
  - Asynchronous Speech-to-Text transcription with support for 100+ languages.
  - Media uploading for local files and resource streams.
  - Speaker Diarization (speaker detection and labeling).
  - Audio Intelligence (Auto Chapters, Sentiment Analysis, Named Entity Detection, Summarization, Content Safety).
  - PII Redaction and Redacted Audio generation.
  - Subtitles export in **SRT** and **VTT** formats.
  - **LeMUR** (Large Language Models applied to speech: custom prompts, Q&A, summaries, and action item extraction).
  - Temporary token generation for secure client-side Real-Time WebSockets streaming.

---

## Installation

Install via Composer:

```bash
composer require alex-volga/assemblyai-php
```

> **Requirements:**
> - PHP 8.3 or higher
> - `ext-json` and `ext-curl` (or an alternative PSR-18 HTTP client)

---

## Quickstart

### Transcribe a local file or remote URL in one step

The high-level `transcribe()` method automatically uploads a local file or stream, dispatches the transcription job, polls until completion, and returns a typed `Transcript` object:

```php
use AssemblyAI\AssemblyAI;

$client = new AssemblyAI('YOUR_ASSEMBLYAI_API_KEY');

// Pass a local file path, open stream resource, or public audio URL:
$transcript = $client->transcribe('/path/to/podcast.mp3');

echo "Status: " . $transcript->status->value . PHP_EOL;
echo "Text: " . $transcript->text . PHP_EOL;
echo "Duration: " . $transcript->audioDuration . " seconds" . PHP_EOL;
```

---

## Client Configuration

### Initialization

```php
use AssemblyAI\AssemblyAI;
use AssemblyAI\Config;

// Basic initialization:
$client = new AssemblyAI('YOUR_API_KEY');

// Or configure using the Config object:
$config = new Config(
    apiKey: 'YOUR_API_KEY',
    baseUrl: Config::DEFAULT_BASE_URL, // or Config::EU_BASE_URL
    timeout: 60.0,                     // HTTP request timeout in seconds
    pollInterval: 3,                   // Polling check interval in seconds
    pollTimeout: 600,                  // Maximum time to wait for completion in seconds
    additionalHeaders: ['X-Custom-Header' => 'value']
);

$client = new AssemblyAI($config);

// Factory method for EU Data Residency:
$clientEu = AssemblyAI::createEu('YOUR_API_KEY');
```

### Using a Custom PSR-18 HTTP Client

The SDK is not locked into a single HTTP client. Inject any `Psr\Http\Client\ClientInterface` implementation:

```php
use AssemblyAI\AssemblyAI;
use Symfony\Component\HttpClient\Psr18Client;

$psrClient = new Psr18Client();
$client = new AssemblyAI(config: 'YOUR_API_KEY', httpClient: $psrClient);
```

---

## Transcripts API

### Uploading Media Files

If your media file is stored locally, upload it to AssemblyAI's servers to obtain an `upload_url`:

```php
// Upload via local file path:
$uploadUrl = $client->files()->upload('/path/to/meeting.mp3');

// Or upload a stream resource:
$stream = fopen('php://temp', 'r+');
// ...
$uploadUrl = $client->files()->upload($stream);
```

### Creating Transcripts with Advanced Options

Use the `TranscriptParams` builder to configure transcription options:

```php
use AssemblyAI\Enums\SpeechModel;
use AssemblyAI\Enums\SummaryModel;
use AssemblyAI\Enums\SummaryType;
use AssemblyAI\Enums\PiiPolicy;
use AssemblyAI\Enums\PiiSubstitution;
use AssemblyAI\Parameters\TranscriptParams;

$params = TranscriptParams::create($uploadUrl)
    // Speaker Diarization
    ->withSpeakerLabels(enabled: true, speakersExpected: 2)
    // Speech Model and Language
    ->withSpeechModel(SpeechModel::Universal2)
    ->withLanguageCode('en_us') // or ->withLanguageDetection(true)
    // Formatting & Punctuation
    ->withPunctuate(true)
    ->withFormatText(true)
    // Audio Intelligence
    ->withAutoChapters(true)
    ->withSentimentAnalysis(true)
    ->withEntityDetection(true)
    ->withSummarization(SummaryType::Bullets, SummaryModel::Informative)
    // PII Redaction
    ->withPiiRedaction(
        policies: [PiiPolicy::EmailAddress, PiiPolicy::PhoneNumber, PiiPolicy::CreditCardNumber],
        sub: PiiSubstitution::EntityType
    )
    // Contextual Prompting & Word Boosting
    ->withPrompt('Technical podcast discussing machine learning and software architecture')
    ->withWordBoost(['AssemblyAI', 'PHP', 'SDK'], 'high');

$created = $client->transcripts()->create($params);
echo "Job ID: " . $created->id; // Status: queued
```

### Polling for Completion

```php
$transcript = $client->transcripts()->waitUntilReady(
    id: $created->id,
    pollInterval: 2, // poll every 2 seconds
    timeout: 300,    // timeout after 5 minutes
    onPoll: function ($current) {
        echo "Current status: {$current->status->value}\n";
    }
);

if ($transcript->isCompleted()) {
    echo "Transcript Text: " . $transcript->text;
}
```

### Inspecting Transcript Results

The `Transcript` object exposes typed Value Objects:

```php
// Words with timestamps and confidence scores:
foreach ($transcript->words as $word) {
    echo "{$word->start}ms - {$word->end}ms: {$word->text} (confidence: {$word->confidence})\n";
}

// Speaker turns (Utterances):
foreach ($transcript->utterances as $utterance) {
    echo "Speaker {$utterance->speaker}: {$utterance->text}\n";
}

// Auto Chapters:
foreach ($transcript->chapters as $chapter) {
    echo "Chapter: {$chapter->headline}\n";
    echo "Summary: {$chapter->summary}\n";
    echo "Time: {$chapter->start}ms - {$chapter->end}ms\n\n";
}

// Sentiment Analysis:
foreach ($transcript->sentimentAnalysisResults as $sentiment) {
    echo "[{$sentiment->sentiment}] {$sentiment->text}\n";
}

// Named Entity Detection:
foreach ($transcript->entities as $entity) {
    echo "Entity: {$entity->text} (type: {$entity->entityType})\n";
}
```

### Sentences and Paragraphs

```php
// Sentences with individual timestamps:
$sentencesResponse = $client->transcripts()->sentences($transcriptId);
foreach ($sentencesResponse->sentences as $sentence) {
    echo $sentence->text . PHP_EOL;
}

// Paragraphs:
$paragraphsResponse = $client->transcripts()->paragraphs($transcriptId);
foreach ($paragraphsResponse->paragraphs as $paragraph) {
    echo $paragraph->text . PHP_EOL;
}
```

### Exporting Subtitles (SRT / VTT)

```php
use AssemblyAI\Enums\SubtitleFormat;

// Export in SRT format:
$srt = $client->transcripts()->subtitles(
    id: $transcriptId,
    format: SubtitleFormat::Srt,
    charsPerCaption: 32 // optional maximum characters per caption line
);
file_put_contents('subtitles.srt', $srt);

// Export in WebVTT format:
$vtt = $client->transcripts()->subtitles($transcriptId, SubtitleFormat::Vtt);
file_put_contents('subtitles.vtt', $vtt);
```

### Word Search in Transcripts

```php
$search = $client->transcripts()->wordSearch($transcriptId, ['artificial', 'intelligence']);
echo "Total occurrences found: " . $search->totalCount;
```

### Listing and Deleting Transcripts

```php
use AssemblyAI\Parameters\TranscriptListParams;
use AssemblyAI\Enums\TranscriptStatus;

// List transcripts with optional filters:
$list = $client->transcripts()->list(new TranscriptListParams(
    limit: 10,
    status: TranscriptStatus::Completed
));

foreach ($list->items as $item) {
    echo "ID: {$item->id}, Status: {$item->status->value}, Created: {$item->created}\n";
}

// Delete a transcript:
$client->transcripts()->delete($transcriptId);
```

---

## LeMUR (LLMs on Speech)

Apply Large Language Models (such as Claude 3.5 Sonnet) directly across one or more transcripts.

### Custom Prompt (Task)

```php
use AssemblyAI\Enums\LemurModel;
use AssemblyAI\Parameters\LemurTaskParams;

$response = $client->lemur()->task(new LemurTaskParams(
    transcriptIds: [$transcriptId],
    prompt: 'Provide a concise summary of the key meeting takeaways and decisions made.',
    finalModel: LemurModel::Claude35Sonnet
));

echo $response->response;
echo "Input tokens used: " . $response->usage?->inputTokens;
```

### Question & Answer (Q&A)

```php
use AssemblyAI\Parameters\LemurQnAParams;

$response = $client->lemur()->questionAnswer(new LemurQnAParams(
    transcriptIds: [$transcriptId],
    questions: [
        ['question' => 'What deadlines were agreed upon?'],
        ['question' => 'Who is responsible for QA testing?'],
    ]
));

echo $response->response;
```

### Action Items Extraction

```php
use AssemblyAI\Parameters\LemurActionItemsParams;

$response = $client->lemur()->actionItems(new LemurActionItemsParams(
    transcriptIds: [$transcriptId]
));

echo $response->response;
```

### Purging LeMUR Response Data

```php
$client->lemur()->purgeData($response->requestId);
```

---

## Real-Time Streaming (Temporary WebSocket Tokens)

To connect browser or mobile client applications to the AssemblyAI Real-Time WebSocket transcription service securely without exposing your permanent API key, generate a single-use temporary token:

```php
// Generate a token valid for opening a session within 60 seconds:
$token = $client->realtime()->createTemporaryToken(
    expiresInSeconds: 60,
    maxSessionDurationSeconds: 3600 // max session length of 1 hour
);

// Pass this token to your client application:
// wss://streaming.assemblyai.com/v3/ws?token={token}
```

---

## Error Handling

The SDK provides a clean, typed exception hierarchy rooted in `AssemblyAI\Exceptions\AssemblyAIException`:

- `AssemblyAIException` (base interface)
  - `ClientException` (base runtime exception)
    - `AuthenticationException` — authentication error (401/403 invalid API key)
    - `NotFoundException` — resource or transcript not found (404)
    - `ValidationException` — invalid request parameters (400/422)
    - `RateLimitException` — rate limit exceeded (429)
    - `ServerException` — AssemblyAI internal server error (5xx)
    - `TranscriptionFailedException` — transcript failed processing with status `error`
    - `TimeoutException` — polling exceeded timeout limit in `waitUntilReady`

```php
use AssemblyAI\Exceptions\AuthenticationException;
use AssemblyAI\Exceptions\TranscriptionFailedException;
use AssemblyAI\Exceptions\TimeoutException;
use AssemblyAI\Exceptions\AssemblyAIException;

try {
    $transcript = $client->transcribe('/path/to/audio.mp3');
} catch (AuthenticationException $e) {
    echo "Authentication failed: " . $e->getMessage();
} catch (TranscriptionFailedException $e) {
    echo "Transcription failed: " . $e->getMessage();
    echo "Transcript ID: " . $e->getTranscriptId();
} catch (TimeoutException $e) {
    echo "Polling timed out: " . $e->getMessage();
} catch (AssemblyAIException $e) {
    echo "SDK error: " . $e->getMessage();
}
```

---

## Testing & Static Analysis

```bash
# Run Unit and Feature tests with PHPUnit
vendor/bin/phpunit

# Run static analysis with PHPStan (Level 8)
vendor/bin/phpstan analyse
```

---

## License

MIT License. See [LICENSE](LICENSE) for details.
