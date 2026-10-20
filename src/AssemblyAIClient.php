<?php

declare(strict_types=1);

namespace AssemblyAI;

use AssemblyAI\Http\HttpClient;
use AssemblyAI\Parameters\TranscriptParams;
use AssemblyAI\Resources\FileResource;
use AssemblyAI\Resources\LemurResource;
use AssemblyAI\Resources\RealtimeResource;
use AssemblyAI\Resources\TranscriptResource;
use AssemblyAI\ValueObjects\Transcript;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class AssemblyAIClient
{
    private readonly Config $config;
    private readonly HttpClient $httpClient;
    private ?FileResource $fileResource = null;
    private ?TranscriptResource $transcriptResource = null;
    private ?LemurResource $lemurResource = null;
    private ?RealtimeResource $realtimeResource = null;

    public function __construct(
        string|Config $config,
        ?PsrClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->config = is_string($config) ? new Config(apiKey: $config) : $config;
        $this->httpClient = new HttpClient(
            config: $this->config,
            client: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
        );
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    public function transcripts(): TranscriptResource
    {
        return $this->transcriptResource ??= new TranscriptResource(
            $this->httpClient,
            $this->config,
            $this->files(),
        );
    }

    public function files(): FileResource
    {
        return $this->fileResource ??= new FileResource($this->httpClient, $this->config);
    }

    public function lemur(): LemurResource
    {
        return $this->lemurResource ??= new LemurResource($this->httpClient, $this->config);
    }

    public function realtime(): RealtimeResource
    {
        return $this->realtimeResource ??= new RealtimeResource($this->httpClient, $this->config);
    }

    /**
     * Upload an audio file or stream.
     *
     * @param string|resource|StreamInterface $file
     */
    public function upload(mixed $file): string
    {
        return $this->files()->upload($file);
    }

    /**
     * Convenience method to transcribe an audio file or URL, wait for completion, and return the Transcript.
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
        return $this->transcripts()->transcribe(
            audio: $audio,
            params: $params,
            pollInterval: $pollInterval,
            timeout: $timeout,
            onPoll: $onPoll,
        );
    }
}
