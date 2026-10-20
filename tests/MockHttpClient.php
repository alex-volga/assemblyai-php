<?php

declare(strict_types=1);

namespace AssemblyAI\Tests;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockHttpClient implements ClientInterface
{
    /** @var list<RequestInterface> */
    public array $requests = [];

    /** @var list<ResponseInterface|\Closure(RequestInterface): ResponseInterface> */
    private array $queue = [];

    /**
     * @param list<ResponseInterface|\Closure(RequestInterface): ResponseInterface> $responses
     */
    public function __construct(array $responses = [])
    {
        $this->queue = $responses;
    }

    public function addResponse(ResponseInterface|\Closure $response): self
    {
        $this->queue[] = $response;
        return $this;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function addJsonResponse(int $status, array $data, array $headers = []): self
    {
        $body = json_encode($data, JSON_THROW_ON_ERROR);
        $headers['Content-Type'] = 'application/json';

        $this->queue[] = new GuzzleResponse($status, $headers, $body);
        return $this;
    }

    /**
     * @param array<string, string> $headers
     */
    public function addStringResponse(int $status, string $body, array $headers = []): self
    {
        $this->queue[] = new GuzzleResponse($status, $headers, $body);
        return $this;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;

        if ($this->queue === []) {
            return new GuzzleResponse(200, ['Content-Type' => 'application/json'], '{}');
        }

        $next = array_shift($this->queue);
        if ($next instanceof \Closure) {
            return $next($request);
        }

        return $next;
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->requests === [] ? null : $this->requests[array_key_last($this->requests)];
    }
}
