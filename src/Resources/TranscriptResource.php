<?php

declare(strict_types=1);

namespace AssemblyAI\Resources;

use AssemblyAI\Config;
use AssemblyAI\Enums\SubtitleFormat;
use AssemblyAI\Exceptions\ClientException;
use AssemblyAI\Exceptions\TimeoutException;
use AssemblyAI\Exceptions\TranscriptionFailedException;
use AssemblyAI\Http\HttpClient;
use AssemblyAI\Parameters\TranscriptListParams;
use AssemblyAI\Parameters\TranscriptParams;
use AssemblyAI\ValueObjects\ParagraphsResponse;
use AssemblyAI\ValueObjects\RedactedAudioResponse;
use AssemblyAI\ValueObjects\SentencesResponse;
use AssemblyAI\ValueObjects\Transcript;
use AssemblyAI\ValueObjects\TranscriptDeleteResponse;
use AssemblyAI\ValueObjects\TranscriptList;
use AssemblyAI\ValueObjects\WordSearchResponse;
use Psr\Http\Message\StreamInterface;

final class TranscriptResource extends AbstractResource
{
    public function __construct(
        HttpClient $http,
        Config $config,
        private readonly ?FileResource $fileResource = null,
    ) {
        parent::__construct($http, $config);
    }

    /**
     * Submit an audio file for transcription.
     *
     * @param TranscriptParams|array<string, mixed> $params
     */
    public function create(TranscriptParams|array $params): Transcript
    {
        $payload = $params instanceof TranscriptParams ? $params->toArray() : $params;

        if (!isset($payload['audio_url']) || !is_string($payload['audio_url']) || trim($payload['audio_url']) === '') {
            throw new ClientException('Audio URL is required to create a transcript.');
        }

        $response = $this->http->post('/v2/transcript', $payload);

        return Transcript::fromArray($response->json());
    }

    /**
     * Get the current status and results of a transcript.
     */
    public function get(string $id): Transcript
    {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        $response = $this->http->get('/v2/transcript/' . rawurlencode($id));

        return Transcript::fromArray($response->json());
    }

    /**
     * Delete a transcript from AssemblyAI servers.
     */
    public function delete(string $id): TranscriptDeleteResponse
    {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        $response = $this->http->delete('/v2/transcript/' . rawurlencode($id));

        return TranscriptDeleteResponse::fromArray($response->json());
    }

    /**
     * List previously submitted transcripts.
     */
    public function list(?TranscriptListParams $params = null): TranscriptList
    {
        $query = $params?->toQueryParams() ?? [];
        $response = $this->http->get('/v2/transcript', $query);

        return TranscriptList::fromArray($response->json());
    }

    /**
     * Get timestamps and texts segmented into sentences.
     */
    public function sentences(string $id): SentencesResponse
    {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        $response = $this->http->get(sprintf('/v2/transcript/%s/sentences', rawurlencode($id)));

        return SentencesResponse::fromArray($response->json());
    }

    /**
     * Get timestamps and texts segmented into paragraphs.
     */
    public function paragraphs(string $id): ParagraphsResponse
    {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        $response = $this->http->get(sprintf('/v2/transcript/%s/paragraphs', rawurlencode($id)));

        return ParagraphsResponse::fromArray($response->json());
    }

    /**
     * Export subtitles in SRT or VTT format.
     */
    public function subtitles(
        string $id,
        SubtitleFormat|string $format = SubtitleFormat::Srt,
        ?int $charsPerCaption = null,
    ): string {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        $formatStr = $format instanceof SubtitleFormat ? $format->value : strtolower($format);
        if (!in_array($formatStr, ['srt', 'vtt'], true)) {
            throw new ClientException(sprintf('Invalid subtitle format: "%s". Allowed: "srt", "vtt".', $formatStr));
        }

        $queryParams = [];
        if ($charsPerCaption !== null) {
            $queryParams['chars_per_caption'] = $charsPerCaption;
        }

        $response = $this->http->get(
            path: sprintf('/v2/transcript/%s/%s', rawurlencode($id), $formatStr),
            queryParams: $queryParams,
            headers: ['Accept' => 'text/plain'],
        );

        return $response->body();
    }

    /**
     * Search for words within a transcript.
     *
     * @param list<string> $words
     */
    public function wordSearch(string $id, array $words): WordSearchResponse
    {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        if ($words === []) {
            throw new ClientException('Words array cannot be empty for word search.');
        }

        $response = $this->http->get(
            path: sprintf('/v2/transcript/%s/word-search', rawurlencode($id)),
            queryParams: ['words' => implode(',', $words)],
        );

        return WordSearchResponse::fromArray($response->json());
    }

    /**
     * Get redacted audio status or URL for a completed redacted audio request.
     */
    public function redactedAudio(string $id): RedactedAudioResponse
    {
        $id = trim($id);
        if ($id === '') {
            throw new ClientException('Transcript ID cannot be empty.');
        }

        $response = $this->http->get(sprintf('/v2/transcript/%s/redacted-audio', rawurlencode($id)));

        return RedactedAudioResponse::fromArray($response->json());
    }

    /**
     * Poll a transcript until processing completes or fails.
     *
     * @param string $id Transcript ID
     * @param int|null $pollInterval Interval in seconds between checks
     * @param int|null $timeout Maximum time in seconds to wait
     * @param callable(Transcript): void|null $onPoll Callback invoked after each poll
     * @throws TranscriptionFailedException If the transcription status is error
     * @throws TimeoutException If timeout is reached
     */
    public function waitUntilReady(
        string $id,
        ?int $pollInterval = null,
        ?int $timeout = null,
        ?callable $onPoll = null,
    ): Transcript {
        $interval = $pollInterval ?? $this->config->pollInterval;
        $maxWait = $timeout ?? $this->config->pollTimeout;
        $startTime = microtime(true);

        while (true) {
            $transcript = $this->get($id);

            if ($onPoll !== null) {
                $onPoll($transcript);
            }

            if ($transcript->isCompleted()) {
                return $transcript;
            }

            if ($transcript->isError()) {
                throw new TranscriptionFailedException(
                    message: sprintf('Transcription failed: %s', $transcript->error ?? 'Unknown error'),
                    transcriptId: $transcript->id,
                    responseBody: $transcript->raw,
                );
            }

            if ((microtime(true) - $startTime) >= $maxWait) {
                throw new TimeoutException(
                    sprintf('Transcription timed out after waiting %d seconds for ID: %s', $maxWait, $id)
                );
            }

            sleep($interval);
        }
    }

    /**
     * High-level convenience method:
     * Accepts a local file path, stream, or remote audio URL.
     * Uploads the file if local, submits transcription job, polls until completion, and returns the result.
     *
     * @param string|resource|StreamInterface $audio
     * @param TranscriptParams|array<string, mixed>|null $params
     * @param callable(Transcript): void|null $onPoll
     */
    public function transcribe(
        mixed $audio,
        TranscriptParams|array|null $params = null,
        ?int $pollInterval = null,
        ?int $timeout = null,
        ?callable $onPoll = null,
    ): Transcript {
        $audioUrl = null;

        if (is_string($audio) && (str_starts_with($audio, 'http://') || str_starts_with($audio, 'https://'))) {
            $audioUrl = $audio;
        } else {
            $uploader = $this->fileResource ?? new FileResource($this->http, $this->config);
            $audioUrl = $uploader->upload($audio);
        }

        if ($params === null) {
            $transcriptParams = TranscriptParams::create($audioUrl);
        } elseif ($params instanceof TranscriptParams) {
            $transcriptParams = $params;
            $transcriptParams->audioUrl = $audioUrl;
        } else {
            $transcriptParams = $params;
            $transcriptParams['audio_url'] = $audioUrl;
        }

        $created = $this->create($transcriptParams);

        return $this->waitUntilReady($created->id, $pollInterval, $timeout, $onPoll);
    }
}
