# AssemblyAI PHP SDK

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.3-8892BF.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

Сучасний, строго типізований PHP SDK для роботи з API транскрипції та аудіо-аналітики [AssemblyAI](https://www.assemblyai.com).

Пакет спроєктовано з дотриманням сучасних стандартів PHP 8.3+:
- **PHP 8.3+**: readonly класи та DTO, типізовані константи, Backed Enums, pattern matching (`match`), строга типізація (`declare(strict_types=1)`).
- **PSR-сумісність**: PSR-7 (HTTP Messages), PSR-17 (HTTP Factories), PSR-18 (HTTP Client). Ви можете використовувати будь-який сумісний клієнт (Guzzle, Symfony HTTP Client тощо).
- **Повне покриття API**:
  - Асинхронна транскрипція (Speech-to-Text) з підтримкою понад 100 мов.
  - Завантаження локальних аудіофайлів та стрімів.
  - Speaker Diarization (визначення спікерів).
  - Audio Intelligence (розділи Auto Chapters, аналіз тональності Sentiment Analysis, виявлення сутностей Named Entity Detection, автоматичне узагальнення).
  - Редагування конфіденційних даних (PII Redaction) та створення аудіо з прихованими даними.
  - Експорт субтитрів у форматах **SRT** та **VTT**.
  - **LeMUR** (Large Language Models для аналізу транскрипцій: довільні промпти, Q&A, резюме, екстракція action items).
  - Генерація тимчасових токенів для безпечного WebSockets Real-Time стрімінгу на клієнті.

---

## Встановлення (Installation)

Встановіть пакет через Composer:

```bash
composer require assemblyai/assemblyai-php
```

> **Вимоги:**
> - PHP 8.3 або новіша версія
> - Розширення `json` та `curl`

---

## Швидкий старт (Quickstart)

### 1. Транскрипція локального файлу або URL в один рядок

Метод `transcribe()` автоматично завантажує локальний файл (якщо передано шлях або стрім), запускає задачу транскрипції, очікує завершення обробки і повертає готовий DTO `Transcript`:

```php
use AssemblyAI\AssemblyAI;

$client = new AssemblyAI('YOUR_ASSEMBLYAI_API_KEY');

// Можна передати шлях до локального файлу або публічний URL:
$transcript = $client->transcribe('/path/to/podcast.mp3');

echo "Статус: " . $transcript->status->value . PHP_EOL;
echo "Текст: " . $transcript->text . PHP_EOL;
echo "Тривалість: " . $transcript->audioDuration . " сек" . PHP_EOL;
```

---

## Клієнт та конфігурація

### Ініціалізація

```php
use AssemblyAI\AssemblyAI;
use AssemblyAI\Config;

// Базова ініціалізація:
$client = new AssemblyAI('YOUR_API_KEY');

// Або через об'єкт Config для тонкого налаштування:
$config = new Config(
    apiKey: 'YOUR_API_KEY',
    baseUrl: Config::DEFAULT_BASE_URL, // або Config::EU_BASE_URL
    timeout: 60.0,                     // таймаут HTTP-запитів
    pollInterval: 3,                   // інтервал опитування (сек)
    pollTimeout: 600,                  // максимальний час очікування готовності (сек)
    additionalHeaders: ['X-Custom-Header' => 'value']
);

$client = new AssemblyAI($config);

// Або фабричний метод для серверів ЄС (EU data residency):
$clientEu = AssemblyAI::createEu('YOUR_API_KEY');
```

### Використання власного PSR-18 HTTP-клієнта

SDK не прив'язаний жорстко до одного HTTP-клієнта. Ви можете передати будь-яку реалізацію `Psr\Http\Client\ClientInterface`:

```php
use AssemblyAI\AssemblyAI;
use Symfony\Component\HttpClient\Psr18Client;

$psrClient = new Psr18Client();
$client = new AssemblyAI(config: 'YOUR_API_KEY', httpClient: $psrClient);
```

---

## Робота з транскрипціями (Transcripts)

### Завантаження файлу на сервери AssemblyAI

Якщо аудіофайл знаходиться на вашому сервері, завантажте його для отримання `upload_url`:

```php
// Завантаження через шлях до файлу:
$uploadUrl = $client->files()->upload('/path/to/meeting.mp3');

// Або завантаження ресурсу стріму:
$stream = fopen('php://temp', 'r+');
// ...
$uploadUrl = $client->files()->upload($stream);
```

### Створення транскрипції з розширеними параметрами

Клас `TranscriptParams` реалізує зручний fluent builder:

```php
use AssemblyAI\Enums\SpeechModel;
use AssemblyAI\Enums\SummaryModel;
use AssemblyAI\Enums\SummaryType;
use AssemblyAI\Enums\PiiPolicy;
use AssemblyAI\Enums\PiiSubstitution;
use AssemblyAI\Parameters\TranscriptParams;

$params = TranscriptParams::create($uploadUrl)
    // Визначення спікерів (Speaker Diarization)
    ->withSpeakerLabels(enabled: true, speakersExpected: 2)
    // Модель та мова
    ->withSpeechModel(SpeechModel::Universal2)
    ->withLanguageCode('uk') // або ->withLanguageDetection(true)
    // Пунктуація та форматування тексту
    ->withPunctuate(true)
    ->withFormatText(true)
    // Audio Intelligence
    ->withAutoChapters(true)
    ->withSentimentAnalysis(true)
    ->withEntityDetection(true)
    ->withSummarization(SummaryType::Bullets, SummaryModel::Informative)
    // Редагування PII
    ->withPiiRedaction(
        policies: [PiiPolicy::EmailAddress, PiiPolicy::PhoneNumber, PiiPolicy::CreditCardNumber],
        sub: PiiSubstitution::EntityType
    )
    // Підказка контексту для покращення точності
    ->withPrompt('Подкаст про штучний інтелект та програмування')
    ->withWordBoost(['AssemblyAI', 'PHP', 'SDK'], 'high');

$created = $client->transcripts()->create($params);
echo "ID задачі: " . $created->id; // Стан: queued
```

### Очікування результату (Polling)

```php
$transcript = $client->transcripts()->waitUntilReady(
    id: $created->id,
    pollInterval: 2, // опитувати кожні 2 секунди
    timeout: 300,    // очікувати максимум 5 хвилин
    onPoll: function ($current) {
        echo "Поточний статус: {$current->status->value}\n";
    }
);

if ($transcript->isCompleted()) {
    echo "Текст: " . $transcript->text;
}
```

### Робота з результатами транскрипції

Об'єкт `Transcript` повертає типізовані Value Objects:

```php
// Список слів із часовими мітками та впевненістю:
foreach ($transcript->words as $word) {
    echo "{$word->start}ms - {$word->end}ms: {$word->text} (впевненість: {$word->confidence})\n";
}

// Висловлювання окремих спікерів:
foreach ($transcript->utterances as $utterance) {
    echo "Спікер {$utterance->speaker}: {$utterance->text}\n";
}

// Автоматичні розділи (Auto Chapters):
foreach ($transcript->chapters as $chapter) {
    echo "Розділ: {$chapter->headline}\n";
    echo "Короткий зміст: {$chapter->summary}\n";
    echo "Таймінг: {$chapter->start}ms - {$chapter->end}ms\n\n";
}

// Аналіз тональності (Sentiment Analysis):
foreach ($transcript->sentimentAnalysisResults as $sentiment) {
    echo "[{$sentiment->sentiment}] {$sentiment->text}\n";
}

// Виявлені сутності (Named Entities):
foreach ($transcript->entities as $entity) {
    echo "Сутність: {$entity->text} (тип: {$entity->entityType})\n";
}
```

### Отримання речень та абзаців

```php
// Окремі речення:
$sentencesResponse = $client->transcripts()->sentences($transcriptId);
foreach ($sentencesResponse->sentences as $sentence) {
    echo $sentence->text . PHP_EOL;
}

// Окремі абзаци:
$paragraphsResponse = $client->transcripts()->paragraphs($transcriptId);
foreach ($paragraphsResponse->paragraphs as $paragraph) {
    echo $paragraph->text . PHP_EOL;
}
```

### Експорт субтитрів (SRT / VTT)

```php
use AssemblyAI\Enums\SubtitleFormat;

// Експорт у форматі SRT:
$srt = $client->transcripts()->subtitles(
    id: $transcriptId,
    format: SubtitleFormat::Srt,
    charsPerCaption: 32 // необов'язково: обмеження символів на рядок
);
file_put_contents('subtitles.srt', $srt);

// Експорт у форматі VTT:
$vtt = $client->transcripts()->subtitles($transcriptId, SubtitleFormat::Vtt);
file_put_contents('subtitles.vtt', $vtt);
```

### Пошук слів у транскрипції

```php
$search = $client->transcripts()->wordSearch($transcriptId, ['штучний', 'інтелект']);
echo "Знайдено входжень: " . $search->totalCount;
```

### Список транскрипцій та видалення

```php
use AssemblyAI\Parameters\TranscriptListParams;
use AssemblyAI\Enums\TranscriptStatus;

// Список із фільтрами:
$list = $client->transcripts()->list(new TranscriptListParams(
    limit: 10,
    status: TranscriptStatus::Completed
));

foreach ($list->items as $item) {
    echo "ID: {$item->id}, Статус: {$item->status->value}, Створено: {$item->created}\n";
}

// Видалення транскрипції:
$client->transcripts()->delete($transcriptId);
```

---

## LeMUR (LLM на основі аудіо)

LeMUR дозволяє запускати великі мовні моделі (наприклад, Claude 3.5 Sonnet) над вашими транскрипціями для довільних запитів, питань, резюме або списків задач.

### Довільний промпт (Task)

```php
use AssemblyAI\Enums\LemurModel;
use AssemblyAI\Parameters\LemurTaskParams;

$response = $client->lemur()->task(new LemurTaskParams(
    transcriptIds: [$transcriptId],
    prompt: 'Склади короткий огляд зустрічі та виділи ключові рішення.',
    finalModel: LemurModel::Claude35Sonnet
));

echo $response->response;
echo "Використано вхідних токенів: " . $response->usage?->inputTokens;
```

### Питання та відповіді (Q&A)

```php
use AssemblyAI\Parameters\LemurQnAParams;

$response = $client->lemur()->questionAnswer(new LemurQnAParams(
    transcriptIds: [$transcriptId],
    questions: [
        ['question' => 'Які дедлайни були узгоджені?'],
        ['question' => 'Хто відповідальний за реліз?'],
    ]
));

echo $response->response;
```

### Action Items (Список завдань)

```php
use AssemblyAI\Parameters\LemurActionItemsParams;

$response = $client->lemur()->actionItems(new LemurActionItemsParams(
    transcriptIds: [$transcriptId]
));

echo $response->response;
```

### Видалення збережених даних LeMUR

```php
$client->lemur()->purgeData($response->requestId);
```

---

## Real-Time Streaming (Тимчасові токени для WebSockets)

Для безпечного підключення браузера або мобільного додатку до WebSocket API транскрипції AssemblyAI у реальному часі без відкриття постійного API-ключа, згенеруйте одноразовий тимчасовий токен:

```php
// Генерація токена з часом дії 60 секунд:
$token = $client->realtime()->createTemporaryToken(
    expiresInSeconds: 60,
    maxSessionDurationSeconds: 3600 // тривалість сесії до 1 години
);

// Передайте цей токен на фронтенд:
// wss://streaming.assemblyai.com/v3/ws?token={token}
```

---

## Обробка помилок (Exceptions)

SDK має чітку ієрархію винятків, які наслідують `AssemblyAI\Exceptions\AssemblyAIException`:

- `AssemblyAIException` (базовий інтерфейс)
  - `ClientException` (базовий клас)
    - `AuthenticationException` — помилка авторизації (401/403: невірний API-ключ)
    - `NotFoundException` — ресурси або транскрипцію не знайдено (404)
    - `ValidationException` — некоректні параметри запиту (400/422)
    - `RateLimitException` — перевищено ліміт запитів (429)
    - `ServerException` — внутрішня помилка серверів AssemblyAI (5xx)
    - `TranscriptionFailedException` — статус транскрипції `error`
    - `TimeoutException` — перевищено ліміт часу очікування у `waitUntilReady`

```php
use AssemblyAI\Exceptions\AuthenticationException;
use AssemblyAI\Exceptions\TranscriptionFailedException;
use AssemblyAI\Exceptions\TimeoutException;
use AssemblyAI\Exceptions\AssemblyAIException;

try {
    $transcript = $client->transcribe('/path/to/audio.mp3');
} catch (AuthenticationException $e) {
    echo "Помилка автентифікації: " . $e->getMessage();
} catch (TranscriptionFailedException $e) {
    echo "Транскрипція завершилась із помилкою: " . $e->getMessage();
    echo "ID задачі: " . $e->getTranscriptId();
} catch (TimeoutException $e) {
    echo "Таймаут очікування: " . $e->getMessage();
} catch (AssemblyAIException $e) {
    echo "Помилка SDK: " . $e->getMessage();
}
```

---

## Тестування та перевірка коду

```bash
# Запуск Unit та Feature тестів
vendor/bin/phpunit

# Статичний аналіз (PHPStan level 8)
vendor/bin/phpstan analyse
```

---

## Ліцензія

MIT License. Детальніше дивіться у файлі [LICENSE](LICENSE).
