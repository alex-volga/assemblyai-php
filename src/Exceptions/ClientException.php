<?php

declare(strict_types=1);

namespace AssemblyAI\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base runtime exception thrown by the AssemblyAI SDK.
 */
class ClientException extends RuntimeException implements AssemblyAIException
{
    /**
     * @param array<string, mixed>|null $responseBody
     */
    public function __construct(
        string $message,
        int $code = 0,
        private readonly ?int $statusCode = null,
        private readonly ?array $responseBody = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
