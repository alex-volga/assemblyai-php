<?php

declare(strict_types=1);

namespace AssemblyAI\Exceptions;

class TranscriptionFailedException extends ClientException
{
    public function __construct(
        string $message,
        private readonly string $transcriptId,
        ?int $statusCode = null,
        ?array $responseBody = null,
    ) {
        parent::__construct($message, 0, $statusCode, $responseBody);
    }

    public function getTranscriptId(): string
    {
        return $this->transcriptId;
    }
}
