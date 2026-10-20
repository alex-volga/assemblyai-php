<?php

declare(strict_types=1);

namespace AssemblyAI\Http;

use AssemblyAI\Config;
use AssemblyAI\Exceptions\AuthenticationException;
use AssemblyAI\Exceptions\ClientException;
use AssemblyAI\Exceptions\NotFoundException;
use AssemblyAI\Exceptions\RateLimitException;
use AssemblyAI\Exceptions\ServerException;
use AssemblyAI\Exceptions\ValidationException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class HttpClient
{
    private const string SDK_VERSION = '1.0.0';

    private PsrClientInterface $client;
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;

    public function __construct(
        private readonly Config $config,
        ?PsrClientInterface $client = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->client = $client ?? new GuzzleClient([
            'timeout' => $config->timeout,
        ]);

        $factory = new HttpFactory();
        $this->requestFactory = $requestFactory ?? $factory;
        $this->streamFactory = $streamFactory ?? $factory;
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, string> $headers
     */
    public function get(string $path, array $queryParams = [], array $headers = []): Response
    {
        $uri = $this->buildUri($path, $queryParams);
        $request = $this->createRequest('GET', $uri, $headers);

        return $this->send($request);
    }

    /**
     * @param array<string, mixed>|null $data
     * @param array<string, string> $headers
     */
    public function post(string $path, ?array $data = null, array $headers = []): Response
    {
        $uri = $this->buildUri($path);
        $request = $this->createRequest('POST', $uri, $headers);

        if ($data !== null) {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
            $stream = $this->streamFactory->createStream($json);
            $request = $request
                ->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }

        return $this->send($request);
    }

    /**
     * @param string|resource|StreamInterface $body
     * @param array<string, string> $headers
     */
    public function upload(string $path, mixed $body, array $headers = []): Response
    {
        $uri = $this->buildUri($path);
        $request = $this->createRequest('POST', $uri, $headers);

        if (is_resource($body)) {
            $stream = $this->streamFactory->createStreamFromResource($body);
        } elseif ($body instanceof StreamInterface) {
            $stream = $body;
        } elseif (is_string($body)) {
            if (file_exists($body) && is_file($body)) {
                $resource = fopen($body, 'rb');
                if ($resource === false) {
                    throw new ClientException(sprintf('Failed to open audio file: %s', $body));
                }
                $stream = $this->streamFactory->createStreamFromResource($resource);
            } else {
                $stream = $this->streamFactory->createStream($body);
            }
        } else {
            throw new ClientException('Invalid body provided for upload. Expected file path, resource or StreamInterface.');
        }

        $request = $request
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withBody($stream);

        return $this->send($request);
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array<string, string> $headers
     */
    public function delete(string $path, array $queryParams = [], array $headers = []): Response
    {
        $uri = $this->buildUri($path, $queryParams);
        $request = $this->createRequest('DELETE', $uri, $headers);

        return $this->send($request);
    }

    /**
     * @param array<string, string> $headers
     */
    private function createRequest(string $method, string $uri, array $headers = []): RequestInterface
    {
        $request = $this->requestFactory->createRequest($method, $uri);

        $defaultHeaders = [
            'Authorization' => $this->config->apiKey,
            'User-Agent' => sprintf('AssemblyAI-PHP-SDK/%s (PHP %s)', self::SDK_VERSION, PHP_VERSION),
            'Accept' => 'application/json',
        ];

        $mergedHeaders = array_merge($defaultHeaders, $this->config->additionalHeaders, $headers);

        foreach ($mergedHeaders as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    /**
     * @param array<string, mixed> $queryParams
     */
    private function buildUri(string $path, array $queryParams = []): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $uri = $path;
        } else {
            $uri = rtrim($this->config->baseUrl, '/') . '/' . ltrim($path, '/');
        }

        if ($queryParams !== []) {
            $query = http_build_query($queryParams);
            $separator = str_contains($uri, '?') ? '&' : '?';
            $uri .= $separator . $query;
        }

        return $uri;
    }

    private function send(RequestInterface $request): Response
    {
        try {
            $psrResponse = $this->client->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ClientException(
                message: 'HTTP request to AssemblyAI failed: ' . $e->getMessage(),
                code: (int) $e->getCode(),
                previous: $e,
            );
        }

        $response = new Response($psrResponse);

        if (!$response->isSuccessful()) {
            $this->handleError($response);
        }

        return $response;
    }

    private function handleError(Response $response): void
    {
        $statusCode = $response->status();
        $body = null;
        $message = sprintf('AssemblyAI API returned HTTP %d', $statusCode);

        try {
            $body = $response->json();
            if (isset($body['error']) && is_string($body['error'])) {
                $message = $body['error'];
            } elseif (isset($body['message']) && is_string($body['message'])) {
                $message = $body['message'];
            }
        } catch (\Throwable) {
            $rawBody = trim($response->body());
            if ($rawBody !== '') {
                $message .= ': ' . substr($rawBody, 0, 200);
            }
        }

        match ($statusCode) {
            401, 403 => throw new AuthenticationException($message, $statusCode, $statusCode, $body),
            404 => throw new NotFoundException($message, $statusCode, $statusCode, $body),
            400, 422 => throw new ValidationException($message, $statusCode, $statusCode, $body),
            429 => throw new RateLimitException($message, $statusCode, $statusCode, $body),
            default => $statusCode >= 500
                ? throw new ServerException($message, $statusCode, $statusCode, $body)
                : throw new ClientException($message, $statusCode, $statusCode, $body),
        };
    }
}
