<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use AssemblyAI\AssemblyAI;
use AssemblyAI\Enums\SpeechModel;
use AssemblyAI\Enums\SubtitleFormat;
use AssemblyAI\Enums\SummaryModel;
use AssemblyAI\Enums\SummaryType;
use AssemblyAI\Parameters\TranscriptParams;

// Retrieve API key from environment variable or fallback:
$apiKey = getenv('ASSEMBLYAI_API_KEY') ?: 'YOUR_API_KEY';
$client = new AssemblyAI($apiKey);

$audioUrl = 'https://storage.googleapis.com/aai-web-samples/5_行业数智化.wav';

echo "1. Starting audio transcription..." . PHP_EOL;

// Create transcription job with parameters:
$params = TranscriptParams::create($audioUrl)
    ->withSpeechModel(SpeechModel::Universal2)
    ->withSpeakerLabels(enabled: true)
    ->withAutoChapters(true)
    ->withSentimentAnalysis(true)
    ->withSummarization(SummaryType::Headline, SummaryModel::Informative);

$transcript = $client->transcribe(
    audio: $audioUrl,
    params: $params,
    pollInterval: 2,
    timeout: 300,
    onPoll: function ($current) {
        echo "   Processing status: {$current->status->value}...\n";
    }
);

echo PHP_EOL . "=== Transcription Results ===" . PHP_EOL;
echo "ID: " . $transcript->id . PHP_EOL;
echo "Text: " . $transcript->text . PHP_EOL;
echo "Audio duration: " . $transcript->audioDuration . " seconds" . PHP_EOL;
echo "Confidence: " . round(($transcript->confidence ?? 0.0) * 100, 2) . "%" . PHP_EOL;

if ($transcript->utterances !== []) {
    echo PHP_EOL . "--- Speaker Turns ---" . PHP_EOL;
    foreach ($transcript->utterances as $utterance) {
        echo "[Speaker {$utterance->speaker}] ({$utterance->start}ms - {$utterance->end}ms): {$utterance->text}" . PHP_EOL;
    }
}

if ($transcript->chapters !== []) {
    echo PHP_EOL . "--- Auto Chapters ---" . PHP_EOL;
    foreach ($transcript->chapters as $chapter) {
        echo "• {$chapter->headline}: {$chapter->summary}" . PHP_EOL;
    }
}

echo PHP_EOL . "2. Exporting SRT Subtitles..." . PHP_EOL;
$srt = $client->transcripts()->subtitles($transcript->id, SubtitleFormat::Srt);
echo substr($srt, 0, 300) . "..." . PHP_EOL;

echo PHP_EOL . "3. Applying LeMUR Task..." . PHP_EOL;
$lemurResponse = $client->lemur()->task([
    'transcript_ids' => [$transcript->id],
    'prompt' => 'Provide a 1-sentence summary of what this audio is about.',
]);

echo "LeMUR Response: " . $lemurResponse->response . PHP_EOL;
