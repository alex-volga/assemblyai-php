<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use AssemblyAI\AssemblyAI;
use AssemblyAI\Enums\SpeechModel;
use AssemblyAI\Enums\SubtitleFormat;
use AssemblyAI\Enums\SummaryModel;
use AssemblyAI\Enums\SummaryType;
use AssemblyAI\Parameters\TranscriptParams;

// Отримуємо API-ключ зі змінних оточення:
$apiKey = getenv('ASSEMBLYAI_API_KEY') ?: 'YOUR_API_KEY';
$client = new AssemblyAI($apiKey);

$audioUrl = 'https://storage.googleapis.com/aai-web-samples/5_行业数智化.wav';

echo "1. Запуск транскрипції аудіо..." . PHP_EOL;

// Створюємо задачу з параметрами:
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
        echo "   Статус обробки: {$current->status->value}...\n";
    }
);

echo PHP_EOL . "=== Результати транскрипції ===" . PHP_EOL;
echo "ID: " . $transcript->id . PHP_EOL;
echo "Текст: " . $transcript->text . PHP_EOL;
echo "Тривалість аудіо: " . $transcript->audioDuration . " сек" . PHP_EOL;
echo "Впевненість розпізнавання: " . round(($transcript->confidence ?? 0.0) * 100, 2) . "%" . PHP_EOL;

if ($transcript->utterances !== []) {
    echo PHP_EOL . "--- Репліки спікерів ---" . PHP_EOL;
    foreach ($transcript->utterances as $utterance) {
        echo "[Спікер {$utterance->speaker}] ({$utterance->start}ms - {$utterance->end}ms): {$utterance->text}" . PHP_EOL;
    }
}

if ($transcript->chapters !== []) {
    echo PHP_EOL . "--- Автоматичні розділи ---" . PHP_EOL;
    foreach ($transcript->chapters as $chapter) {
        echo "• {$chapter->headline}: {$chapter->summary}" . PHP_EOL;
    }
}

echo PHP_EOL . "2. Експорт субтитрів SRT..." . PHP_EOL;
$srt = $client->transcripts()->subtitles($transcript->id, SubtitleFormat::Srt);
echo substr($srt, 0, 300) . "..." . PHP_EOL;

echo PHP_EOL . "3. Застосування LeMUR Q&A..." . PHP_EOL;
$lemurResponse = $client->lemur()->task([
    'transcript_ids' => [$transcript->id],
    'prompt' => 'Provide a 1-sentence summary of what this audio is about.',
]);

echo "Відповідь LeMUR: " . $lemurResponse->response . PHP_EOL;
