<?php

declare(strict_types=1);

namespace AssemblyAI\Http;

use AssemblyAI\Exceptions\ClientException;
use Psr\Http\Message\ResponseInterface;

final readonly class Response
{
    public function __construct(
        private ResponseInterface $psrResponse,
    ) {
    }

    public function status(): int
    {
        return $this->psrResponse->getStatusCode();
    }

    public function isSuccessful(): bool
    {
        $status = $this->status();
        return $status >= 200 && $status < 300;
    }

    public function body(): string
    {
        return (string) $this->psrResponse->getBody();
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        $body = $this->body();
        if (trim($body) === '') {
            return [];
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ClientException(
                message: 'Failed to decode JSON response from AssemblyAI: ' . $e->getMessage(),
                code: (int) $e->getCode(),
                statusCode: $this->status(),
                previous: $e,
            );
        }

        if (!is_array($data)) {
            throw new ClientException(
                message: 'Expected JSON object/array from AssemblyAI response.',
                statusCode: $this->status(),
            );
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    public function getHeaderLine(string $name): string
    {
        return $this->psrResponse->getHeaderLine($name);
    }

    public function getPsrResponse(): ResponseInterface
    {
        return $this->psrResponse;
    }
}
