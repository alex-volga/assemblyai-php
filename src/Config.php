<?php

declare(strict_types=1);

namespace AssemblyAI;

use InvalidArgumentException;

/**
 * Configuration options for the AssemblyAI client.
 */
final readonly class Config
{
    public const string DEFAULT_BASE_URL = 'https://api.assemblyai.com';
    public const string EU_BASE_URL = 'https://api.eu.assemblyai.com';
    public const float DEFAULT_TIMEOUT = 60.0;
    public const int DEFAULT_POLL_INTERVAL = 3;
    public const int DEFAULT_POLL_TIMEOUT = 300;

    /**
     * @param array<string, string> $additionalHeaders
     */
    public function __construct(
        public string $apiKey,
        public string $baseUrl = self::DEFAULT_BASE_URL,
        public float $timeout = self::DEFAULT_TIMEOUT,
        public int $pollInterval = self::DEFAULT_POLL_INTERVAL,
        public int $pollTimeout = self::DEFAULT_POLL_TIMEOUT,
        public array $additionalHeaders = [],
    ) {
        if (trim($this->apiKey) === '') {
            throw new InvalidArgumentException('AssemblyAI API key cannot be empty.');
        }

        if (filter_var($this->baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('Invalid base URL: "%s".', $this->baseUrl));
        }

        if ($this->timeout <= 0) {
            throw new InvalidArgumentException('Timeout must be greater than zero.');
        }

        if ($this->pollInterval <= 0) {
            throw new InvalidArgumentException('Poll interval must be greater than zero.');
        }

        if ($this->pollTimeout <= 0) {
            throw new InvalidArgumentException('Poll timeout must be greater than zero.');
        }
    }

    /**
     * Create a configuration instance pointing to AssemblyAI EU servers.
     *
     * @param array<string, string> $additionalHeaders
     */
    public static function forEu(
        string $apiKey,
        float $timeout = self::DEFAULT_TIMEOUT,
        int $pollInterval = self::DEFAULT_POLL_INTERVAL,
        int $pollTimeout = self::DEFAULT_POLL_TIMEOUT,
        array $additionalHeaders = [],
    ): self {
        return new self(
            apiKey: $apiKey,
            baseUrl: self::EU_BASE_URL,
            timeout: $timeout,
            pollInterval: $pollInterval,
            pollTimeout: $pollTimeout,
            additionalHeaders: $additionalHeaders,
        );
    }
}
