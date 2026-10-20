<?php

declare(strict_types=1);

namespace AssemblyAI;

use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Main entry point for the AssemblyAI PHP SDK.
 */
class AssemblyAI extends AssemblyAIClient
{
    /**
     * Create a new AssemblyAI client instance.
     */
    public static function create(
        string|Config $config,
        ?PsrClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ): AssemblyAIClient {
        return new AssemblyAIClient(
            config: $config,
            httpClient: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
        );
    }

    /**
     * Create a client configured for the European Union (EU) region.
     */
    public static function createEu(
        string $apiKey,
        ?PsrClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ): AssemblyAIClient {
        return new AssemblyAIClient(
            config: Config::forEu($apiKey),
            httpClient: $httpClient,
            requestFactory: $requestFactory,
            streamFactory: $streamFactory,
        );
    }
}
